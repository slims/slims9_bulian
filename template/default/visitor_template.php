<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2020-01-03 08:49
 * @File name           : visitor_template.php
 */

$main_template_path = __DIR__ . '/login_template.inc.php';

// set default language
if (isset($_GET['select_lang'])) {
    $select_lang = trim(strip_tags($_GET['select_lang']));
    // delete previous language cookie
    if (isset($_COOKIE['select_lang'])) {
        @setcookie('select_lang', $select_lang, time()-14400, SWB);
    }
    // create language cookie
    @setcookie('select_lang', $select_lang, time()+14400, SWB);
    $sysconf['default_lang'] = $select_lang;
} else if (isset($_COOKIE['select_lang'])) {
    $sysconf['default_lang'] = trim(strip_tags($_COOKIE['select_lang']));
}

?>
<div class="vegas-slide" style="position: fixed; z-index: -1"></div>
<div class="flex h-screen w-full" id="visitor-counter" style="background: rgba(0,0,0,0.3)">
    <div class="bg-white w-full md:w-1/3 px-8 pt-8 pb-3 flex flex-col justify-between">
        <div>
            <h3 class="font-light mb-2">Welcome to <?= $sysconf['library_name']; ?></h3>
            <p class="lead">
                Please, fill your data into our visitor log.
            </p>

            <div v-if="textInfo !== ''" class="rounded p-2 mt-4 bg-blue-lighter text-blue-darker md:hidden">{{textInfo}}</div>

            <form class="mt-4" @submit.prevent="onSubmit">
                <div class="form-group">
                    <label for="exampleInputEmail1">Member ID</label>
                    <input v-model="memberId" ref="memberId" autofocus type="text" class="form-control" id="exampleInputEmail1"
                           aria-describedby="emailHelp" placeholder="Enter your member ID">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Institution</label>
                    <input v-model="institution" type="text" class="form-control" id="exampleInputPassword1"
                           placeholder="Enter your institution">
                    <small id="emailHelp" class="form-text text-muted">Enough fill your member ID if you are member
                        of <?= $sysconf['library_name']; ?></small>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Check In</button>
            </form>
        </div>
        <div class="text-right">
            <small class="text-grey-dark">Powered by <code>SLiMS</code></small>
        </div>
    </div>
    <div class="flex-1 hidden md:block">
        <div class="h-screen">
            <div v-show="textInfo !== ''" class="flex items-center h-screen p-8">
                <div class="w-32">
                    <div class="w-32 h-32 bg-white rounded-full border-white border-4 shadow">
                        <img :src="image" alt="image" class="rounded-full" @error="onImageError">
                    </div>
                </div>
                <div class="px-8">
                    <h3 class="font-light text-white mb-2">{{textInfo}}</h3>
                    </p>
                </div>
            </div>
            <div class="flex h-screen items-end p-8">
                <blockquote class="blockquote" v-show="textInfo === ''">
                    <p class="text-white">{{quotes.content}}</p>
                    <footer class="blockquote-footer text-grey-light">{{quotes.author}}</footer>
                </blockquote>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/assets/js/axios.min.js'; ?>"></script>
<script>
    new Vue({
        el: '#visitor-counter',
        data() {
            return {
                memberId: '',
                institution: '',
                textInfo: '',
                image: './images/persons/photo.png',
                quotes: {},
                timeout: null
            }
        },
        mounted() {
            this.$refs.memberId.focus()
            this.getQuotes()
        },
        methods: {
            onImageError: function() {
                this.image = './images/persons/photo.png'
            },
            getQuotes: function() {
                axios.get('https://api.quotable.io/random')
                    .then(res => {
                        this.quotes = res.data
                    })
                    .catch(() => {
                        this.quotes = {
                            content: "Sing penting madhiang.",
                            author: "Pai-Jo"
                        }
                    })
                    .finally(() => {
                        this.textInfo = ''
                    })
            },
            onSubmit: function() {
                if (this.memberId === '') {
                    this.resetForm()
                    return
                }
                let url = 'index.php?p=visitor'
                let data = new FormData()
                data.append('memberID', this.memberId)
                data.append('institution', this.institution)
                data.append('counter', 1)

                axios({
                    url: url,
                    method: 'post',
                    data: data,
                    headers: {'Content-Type': 'multipart/form-data' }
                })
                    .then(res => {
                        this.textInfo = res.data
                        this.image = `./images/persons/member_${this.memberId}.jpg`
                        this.textToSpeech(this.textInfo)
                    })
                    .catch(err => {
                        console.log(err);
                    })
                    .finally(() => {
                        this.resetForm()
                        clearTimeout(this.timeout)
                        this.timeout = setTimeout(() => {
                            this.getQuotes()
                        }, 5000)
                    })
            },
            resetForm: function () {
                this.memberId = ''
                this.institution = ''
                this.$refs.memberId.focus()
            },
            textToSpeech: function(message) {
                var message = new SpeechSynthesisUtterance(message);
                var voices = speechSynthesis.getVoices();
                // console.log(message);
                message['volume'] = 1;
                message['rate'] = 1;
                message['pitch'] = 1;
                message['lang'] = '<?php echo str_replace('_', '-', $sysconf['default_lang']); ?>';
                message['voice'] = null;
                speechSynthesis.cancel();
                speechSynthesis.speak(message);
            }
        }
    })
</script>