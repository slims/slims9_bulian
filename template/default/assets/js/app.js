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

const show_advanced = new Vue({
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
            const token = this.$refs["csrf_token"].value
            window.location.href = `index.php?${this.searchBy}=${this.keywords}&search=search&csrf_token=${token}`;
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

Vue.component('slims-subject', {
    props: {
        topic: {
            type: String,
            default: ''
        }
    },
    render: function(createElement) {
        return createElement('a', {
            domProps: {
                innerHTML: this.topic
            },
            attrs: {
                href: `index.php?subject="${encodeURIComponent(this.topic).replace(/%20/g, "+")}"&search=search`,
                class: 'btn btn-outline-secondary btn-rounded btn-sm mr-2 mb-2'
            }
        })
    }
});

Vue.component('slims-book', {
    props: {
        biblioId: {
            type: String,
            default: ''
        },
        title: {
            type: String,
            default: ''
        },
        image: {
            type: String,
            default: ''
        }
    },
    render: function (createElement) {
        return createElement('div', {
            attrs: {
                class: 'w-48 pr-4 pb-4'
            }
        }, [
            createElement('a', {
                attrs: {
                    href: `index.php?p=show_detail&id=${this.biblioId}`,
                    class: 'card border-0 hover:shadow cursor-pointer text-decoration-none h-full'
                },
            }, [
                createElement('div', {
                    attrs: {
                        class: 'card-body'
                    }
                }, [
                    createElement('div', {
                        attrs: {
                            class: 'card-image fit-height'
                        }
                    }, [
                        createElement('img', {
                            attrs: {
                                src: this.image,
                                class: 'img-fluid',
                                loading: 'lazy'
                            }
                        })
                    ]),
                    createElement('div', {
                        attrs: {
                            class: 'card-text mt-2 text-grey-darker'
                        },
                        domProps: {
                            innerHTML: this.title
                        }
                    })
                ])
            ])
        ])
    }
});

Vue.component('slims-member', {
    props: {
        image: {
            type: String,
            default: ''
        },
        memberName: {
            type: String,
            default: ''
        },
        memberType: {
            type: String,
            default: ''
        },
        totalLoan: {
            type: String,
            default: '0'
        },
        totalBiblio: {
            type: String,
            default: '0'
        }
    },
    render: function (createElement) {
        return createElement('div', {
            attrs: {
                class: 'w-full md:w-1/3 px-3 mb-2'
            }
        }, [
            createElement('div', {
                attrs: {
                    class: 'card hover:shadow-md'
                }
            }, [
                createElement('div', {
                    attrs: {
                        class: 'card-body'
                    }
                }, [
                    createElement('div', {
                        attrs: {
                            class: 'card-image-rounded mx-auto'
                        }
                    }, [
                        createElement('img', {
                            attrs: {
                                class: 'img-fluid h-auto',
                                src: this.image,
                                loading: 'lazy'
                            }
                        })
                    ]),
                    createElement('h5', {
                        attrs: {
                            class: 'card-title text-center mt-3'
                        }
                    }, [
                        createElement('span', this.memberName),
                        createElement('br'),
                        createElement('small', {
                            attrs: {
                                class: 'text-grey-darker'
                            },
                            domProps: {
                                innerHTML: this.memberType
                            }
                        })
                    ]),
                    createElement('p', {
                        attrs: {
                            class: 'card-text text-center'
                        }
                    }, [
                        createElement('b', this.totalLoan),
                        createElement('span', {
                            attrs: {
                                class: 'text-grey-darker ml-1'
                            },
                            domProps: {
                                innerHTML: 'Loans'
                            }
                        }),
                        createElement('span', {
                            attrs: {
                                class: 'inline-block h-4 mx-3 relative bg-grey align-middle',
                                style: 'width: 1px'
                            }
                        }),
                        createElement('b', this.totalBiblio),
                        createElement('span', {
                            attrs: {
                                class: 'text-grey-darker ml-1'
                            },
                            domProps: {
                                innerHTML: 'Title'
                            }
                        }),
                    ])
                ])
            ])
        ]);
    }
});

Vue.component('slims-collection', {
    props: {
        url: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            biblios: [],
            loading: false
        }
    },
    mounted() {
        this.getData()
    },
    methods: {
        getData() {
            this.loading = true
            fetch(this.url)
                .then(res => res.json())
                .then(res => {
                    this.biblios = res
                })
                .finally(() => {
                    this.loading = false
                })
        }
    },
    render: function (createElement) {
        if (this.loading && this.biblios.length < 1) {
            return createElement('div', {
                attrs: {
                    class: 'spinner-border text-primary'
                }
            }, [
                createElement('span', {
                    attrs: {
                        class: 'sr-only',
                        role: 'status'
                    },
                    domProps: {
                        innerHTML: 'Loading...'
                    }
                })
            ])
        } else {
            return createElement('div', {
                attrs: {
                    class: 'flex flex-wrap mt-4 collection'
                }
            }, this.biblios.map(function (item) {
                return createElement('slims-book', {
                    attrs: {
                        biblioId: item.biblio_id,
                        image: item.image,
                        title: item.title,
                    }
                })
            }))
        }
    }
});

Vue.component('slims-group-subject', {
    props: {
        url: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            subjects: [],
            loading: false
        }
    },
    mounted() {
        this.getData()
    },
    methods: {
        getData() {
            this.loading = true
            fetch(this.url)
                .then(res => res.json())
                .then(res => {
                    this.subjects = res
                })
                .finally(() => {
                    this.loading = false
                })
        }
    },
    render: function (createElement) {
        if (this.loading && this.subjects.length < 1) {
            return createElement('div', {
                attrs: {
                    class: 'spinner-border text-primary'
                }
            }, [
                createElement('span', {
                    attrs: {
                        class: 'sr-only',
                        role: 'status'
                    },
                    domProps: {
                        innerHTML: 'Loading...'
                    }
                })
            ])
        } else {
            return createElement('div', {
                attrs: {
                    class: 'flex flex-wrap'
                }
            }, this.subjects.map(function (topic) {
                return createElement('slims-subject', {
                    attrs: {
                        topic
                    }
                })
            }))
        }
    }
});

Vue.component('slims-group-member', {
    props: {
        url: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            members: [],
            loading: false
        }
    },
    mounted() {
        this.getData()
    },
    methods: {
        getData() {
            this.loading = true
            fetch(this.url)
                .then(res => res.json())
                .then(res => {
                    this.members = res
                })
                .finally(() => {
                    this.loading = false
                })
        }
    },
    render: function (createElement) {
        if (this.loading && this.members.length < 1) {
            return createElement('div', {
                attrs: {
                    class: 'spinner-border text-primary'
                }
            }, [
                createElement('span', {
                    attrs: {
                        class: 'sr-only',
                        role: 'status'
                    },
                    domProps: {
                        innerHTML: 'Loading...'
                    }
                })
            ])
        } else {
            return createElement('div', {
                attrs: {
                    class: 'flex flex-wrap'
                }
            }, this.members.map(function (member) {
                return createElement('slims-member', {
                    attrs: {
                        memberName: member.name,
                        memberType: member.type,
                        image: member.image,
                        totalLoan: member.total,
                        totalBiblio: member.total_title
                    }
                })
            }))
        }
    }
});

if (document.getElementById('slims-home')) {
    const slimsHome = new Vue({
        el: '#slims-home',
    })
}