<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2018-01-25T10:31:54+07:00
# @Email:  ido.alit@gmail.com
# @Filename: _search-form.php
# @Last modified by:   user
# @Last modified time: 2018-01-26T16:53:56+07:00

?>
<div class="search" id="search-wraper" xmlns:v-bind="http://www.w3.org/1999/xhtml">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow">
                    <div class="card-body">
                        <form class="" action="index.php" method="get" @submit.prevent="searchSubmit">
                            <input type="hidden" name="search" value="search">
                            <input ref="keywords" value="<?php echo getQuery('keywords'); ?>" v-model.trim="keywords"
                                   @focus="searchOnFocus" @blur="searchOnBlur" type="text" id="search-input"
                                   name="keywords" class="input-transparent w-100"
                                   placeholder="Enter keyword to search collection..."/>
                        </form>
                    </div>
                </div>
                <transition name="slide-fade">
                    <div v-if="show" class="advanced-wraper shadow mt-4" id="advanced-wraper"
                         v-click-outside="hideSearch">
                        <p class="label mb-2">
                            Search by:
                            <i @click="hideSearch"
                               class="far fa-times-circle float-right text-danger cursor-pointer"></i>
                        </p>
                        <div class="d-flex flex-wrap">
                            <a v-bind:class="{'btn-primary text-white': searchBy === 'keywords', 'btn-outline-secondary': searchBy !== 'keywords' }"
                               @click="searchOnClick('keywords')" class="btn mr-2 mb-2">All</a>
                            <a v-bind:class="{'btn-primary text-white': searchBy === 'author', 'btn-outline-secondary': searchBy !== 'author' }"
                               @click="searchOnClick('author')" class="btn mr-2 mb-2">Author</a>
                            <a v-bind:class="{'btn-primary text-white': searchBy === 'subject', 'btn-outline-secondary': searchBy !== 'subject' }"
                               @click="searchOnClick('subject')" class="btn mr-2 mb-2">Subject</a>
                            <a v-bind:class="{'btn-primary text-white': searchBy === 'isbn', 'btn-outline-secondary': searchBy !== 'isbn' }"
                               @click="searchOnClick('isbn')" class="btn mr-2 mb-2">ISBN/ISSN</a>
                            <button class="btn btn-light mr-2 mb-2" disabled>OR TRY</button>
                            <a class="btn btn-outline-primary mr-2 mb-2" data-toggle="modal" data-target="#adv-modal">Advanced Search</a>
                        </div>
                        <p v-if="lastKeywords.length > 0" class="label mt-4">Last search:</p>
                        <a :href="`index.php?${tmpObj[k].searchBy}=${tmpObj[k].text}&search=search`"
                           class="flex items-center justify-between py-1 text-decoration-none text-grey-darkest hover:text-blue"
                           v-for="k in lastKeywords" :key="k"><span><i
                                        class="far fa-clock text-grey-dark mr-2"></i><span class="italic text-sm">{{tmpObj[k].text}}</span></span><i
                                    class="fas fa-angle-right text-grey-dark"></i></a>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</div>
