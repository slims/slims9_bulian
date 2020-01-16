/**
 * @Author: Waris Agung Widodo <user>
 * @Date:   2018-01-21T12:15:49+07:00
 * @Email:  ido.alit@gmail.com
 * @Filename: app.js
 * @Last modified by:   user
 * @Last modified time: 2018-01-23T18:22:24+07:00
 */

'use strict';

Vue.directive('click-outside', {
    priority: 700,
    bind: function (el, binding, vnode) {
        window.event = function (event) {
            if (!(el === event.target || el.contains(event.target))) {
                vnode.context[binding.expression](event);
            }
        };
        document.body.addEventListener('click', window.event)
    },
    unbind: function (el) {
        document.body.removeEventListener('click', window.event)
    },
});

var show_advanced = new Vue({
    el: '#search-wraper',
    data: function () {
        return {
            show: false,
            isFocus: false,
            searchBy: 'keywords',
            keywords: '',
            tmpObj: {}
        }
    },
    computed: {
        lastKeywords: function () {
            let raw = localStorage.getItem('keywords')
            if (raw) {
                try {
                    let keywords = JSON.parse(raw), arr = []
                    for (let key in keywords) {
                        if (keywords.hasOwnProperty(key)) {
                            arr.push(keywords[key].time)
                            keywords[key].text = key
                            this.tmpObj[keywords[key].time] = keywords[key]
                        }
                    }
                    arr.sort()
                    arr.reverse()
                    return arr.slice(0, 5)
                } catch (e) {
                    console.error(e.message)
                    return []
                }
            }
            return []
        }
    },
    methods: {
        searchOnFocus: function (e) {
            this.show = true;
            this.isFocus = true;
            const urlParams = new URLSearchParams(window.location.search);
            const search = urlParams.get('search');
            const page = urlParams.get('p');
            if (!search && !page) window.scrollTo(0, 250)
        },
        searchOnBlur: function (e) {
            this.isFocus = false
        },
        hideSearch: function () {
            if (!this.isFocus) {
                this.show = false;
                this.searchBy = 'keywords'
            }
        },
        searchOnClick: function (searchBy) {
            this.searchBy = searchBy
            this.searchSubmit()
        },
        searchSubmit: function () {
            if (this.keywords !== '') this.saveKeyword()
            window.location.href = `index.php?${this.searchBy}=${this.keywords}&search=search`;
        },
        saveKeyword: function () {
            let rawKeywords = localStorage.getItem('keywords')
            let keywords = {};
            if (rawKeywords) {
                try {
                    keywords = JSON.parse(rawKeywords)
                } catch (e) {
                    console.error(e.message)
                }
            }
            if (keywords.hasOwnProperty(this.keywords)) {
                keywords[this.keywords] = {
                    count: keywords[this.keywords].count + 1,
                    searchBy: this.searchBy,
                    time: Date.now()
                }
            } else {
                keywords[this.keywords] = {
                    count: 1,
                    searchBy: this.searchBy,
                    time: Date.now()
                }
            }
            let strKeyword = JSON.stringify(keywords)
            localStorage.setItem('keywords', strKeyword)
        }
    }
});