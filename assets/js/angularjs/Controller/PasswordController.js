(function() {
    app.controller('PasswordTreeController', ["$rootScope", "$scope", "$uibModal", "$state", "PasswordGroupManager", function($rootScope, $scope, $uibModal, $state, PasswordGroupManager) {

        var self = this;
        var sendPasswordSortingTimeout;

        $scope.title = "Tree";
        $scope.list = [];
        $scope.treeOptions = {
            accept: function(sourceNodeScope, destNodesScope, destIndex) {
                return sourceNodeScope.$parentNodesScope.$id === destNodesScope.$id;
            },
            dropped: function (event) {
                clearTimeout(sendPasswordSortingTimeout);
                sendPasswordSortingTimeout = setTimeout(function(){
                    PasswordGroupManager.putPasswordGroupSorting(self.buildSortingTree(event.source.nodesScope.$modelValue));
                }, 700);
            }
        };

        this.buildSortingTree = function(elements) {
            var data = [];

            for (var i = 0; i < elements.length; i++) {
                var value = elements[i];

                data.push({
                    "password_group_id": value.id,
                    "sorting": i
                });

                data = data.concat(self.buildSortingTree(value.nodes));
            }

            return data;
        };

        $scope.loadRootTree = function () {
            PasswordGroupManager.getPasswordGroups().then(function (data) {
                $scope.list = [];
                angular.forEach(data, function (value, key) {
                    $scope.list.push(self.addTree(value));
                });
            });
        };

        this.addTree = function(data) {

            var nodes = [];

            angular.forEach(data.children, function (value, key) {
                nodes.push(self.addTree(value));
            });

            return {
                id: data.id,
                title: data.name,
                icon: data.icon,
                expand: false,
                nodes: nodes
            };
        };

        $scope.openTree = function (nodeId) {
            var found = false;

            angular.forEach($scope.list, function (value, key) {
                if (value.id == nodeId) {
                    found = true;

                    if (value.nodes.length) {
                        value.expand = true;
                    }

                }
            });

            if (found) {
                angular.forEach($scope.list, function (value, key) {
                    if (value.id != nodeId) {
                        value.expand = false;
                    }
                });
            }

            $state.go('overview.group', {groupId: nodeId});
        };

        $scope.loadTree = function (node) {
            node.expand = true;
        };

        $scope.closeNode = function (node) {
            node.expand = false;
        };

        $rootScope.$on('reloadPasswordTree', function(event, mass) {
            $scope.loadRootTree();
        });

        $scope.addPasswordGroup = function () {

            var addPasswordGroupModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/add_or_edit_password_group.html?v=2",
                controller: 'ModalAddOrEditPasswordGroupController',
                size: "lg",
                backdrop: 'static',
                resolve: {
                    passwordGroup: function () {
                        return null;
                    }
                }
            });

            addPasswordGroupModal.result.then(function (response) {
                $scope.loadRootTree();
            }, function() {});
        };

        $scope.getParents = function (parent){

            if (parent.parent !== undefined) {
                return 1 + $scope.getParents(parent.parent);
            }

            return 1;
        };

        $scope.getRepeat = function (value) {

            var buffer = [];

            for (var i=1; i < value; i++) {
                buffer.push(i);
            }

            return buffer;
        };



        $scope.loadRootTree();
    }]);

    app.controller('PasswordGroupRedirectController', ["$state", "PasswordGroupManager", function($state, PasswordGroupManager) {
        PasswordGroupManager.getPasswordGroups().then(function (data) {
            if (data[0] !== undefined) {
                $state.go('overview.group', {groupId: data[0].id});
            }
        });
    }]);

    app.controller('PasswordFilterCtrl', ["$rootScope", "$scope", "$stateParams", "PasswordGroupManager", function($rootScope, $scope, $stateParams, PasswordGroupManager) {

        var self = this;
        self.passwords = null;

        this.currentPasswordGroupId = $stateParams.groupId;

        self.listLoading = false;
        self.page = 0;
        self.limit = 10;

        $scope.loadingPasswordGroup = true;
        $scope.breadcrumbs = [];

        this.addToBreadcrumb = function(passwordGroup) {

            $scope.breadcrumbs.unshift({
                id: passwordGroup.id,
                name: passwordGroup.name
            });

            if (passwordGroup.parent !== null) {
                self.addToBreadcrumb(passwordGroup.parent);
            }
        };

        $scope.passwordGroup = null;

        PasswordGroupManager.getPasswordGroup($stateParams.groupId).then(function (data) {
            $scope.passwordGroup = data;

            self.addToBreadcrumb(data);

            $scope.loadingPasswordGroup = false;
        }, function(res) {
            if (res.status == 403) {
                $scope.accessDenied = true;
            }
        });

        $scope.loadMore = function() {

            if (self.listLoading == false && (self.passwords === null || self.passwords !== null && self.passwords.pages > self.page)) {

                self.listLoading = true;
                self.page += 1;

                self.loadPasswords();
            }
        };

        this.loadPasswords = function(pPage, pLimit, appendPasswords) {
            PasswordGroupManager.getPasswordsByPasswordGroup($stateParams.groupId, { page : pPage ? pPage : self.page, limit : pLimit ? pLimit : self.limit }).then(function (data) {
                if (self.passwords !== null && appendPasswords !== false) {
                    var oldItems = self.passwords;

                    angular.forEach(oldItems, function(value, key) {
                        data.push(value);
                    });
                }

                self.passwords = data;
                $scope.$broadcast('content.changed');
                self.listLoading = false;
            });
        };

        $rootScope.$on('reloadPasswordOverview', function(event, mass) {
            self.passwords = [];
            self.page = 1;
            self.limit = 10;
            self.loadPasswords();
        });

    }]);

    app.controller('PasswordGroupDetailController', ["$rootScope", "$state", "$translate", "$stateParams", "$uibModal", "PasswordGroupManager", "ngNotify", function($rootScope, $state, $translate, $stateParams, $uibModal, PasswordGroupManager, ngNotify) {

        var self = this;

        this.accessDenied = false;
        this.loadingPasswordGroup = true;
        this.breadcrumbs = [];

        this.addToBreadcrumb = function(passwordGroup) {

            self.breadcrumbs.unshift({
                id: passwordGroup.id,
                name: passwordGroup.name
            });

            if (passwordGroup.parent !== null) {
                self.addToBreadcrumb(passwordGroup.parent);
            }
        };

        self.passwordGroup = null;

        PasswordGroupManager.getPasswordGroup($stateParams.groupId).then(function (data) {
            self.passwordGroup = data;

            self.addToBreadcrumb(data);

            self.loadingPasswordGroup = false;
        }, function(res) {
            if (res.status === 403) {
                self.accessDenied = true;
            }
        });

        this.addPassword = function() {
            var addPasswordModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/add_or_edit_password.html?v=2",
                controller: 'ModalAddOrEditPasswordController',
                size: "mg",
                backdrop: 'static',
                resolve: {
                    password: null,
                    passwordGroup: function () {
                        return self.passwordGroup;
                    }
                }
            });

            addPasswordModal.result.then(function (response) {
                self.reloadData();
            }, function() {});
        };

        this.editPasswordGroup = function() {
            var editPasswordGroupModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/add_or_edit_password_group.html?v=2",
                controller: 'ModalAddOrEditPasswordGroupController',
                size: "lg",
                backdrop: 'static',
                resolve: {
                    passwordGroup: function () {
                        return self.passwordGroup;
                    }
                }
            });

            editPasswordGroupModal.result.then(function (response) {}, function() {});
        };

        this.movePasswordGroup = function() {
            var movePasswordGroupModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/move_password_group.html?v=2",
                controller: 'ModalMovePasswordGroupController',
                size: "mg",
                backdrop: 'static',
                resolve: {
                    passwordGroup: function () {
                        return self.passwordGroup;
                    }
                }
            });

            movePasswordGroupModal.result.then(function (response) {}, function() {});
        };

        this.editPasswordGroupAccess = function() {
            var editPasswordGroupAccessModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/edit_password_group_access.html?v=2",
                controller: 'ModalEditAccessPasswordGroupController',
                size: "lg",
                backdrop: 'static',
                resolve: {
                    passwordGroup: function () {
                        return self.passwordGroup;
                    }
                }
            });

            editPasswordGroupAccessModal.result.then(function (response) {}, function() {});
        };

        this.deleteGroup = function(passwordGroup) {
            swal({
                title: $translate.instant('TEXT.ARE_YOU_SURE'),
                text: $translate.instant('PASSWORD_GROUP_MANAGEMENT.TEXT.DELETE_INFORMATION'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $translate.instant('WORDS.YES'),
                cancelButtonText: $translate.instant('WORDS.CLOSE')
            }).then(function() {

                PasswordGroupManager.deletePasswordGroup(passwordGroup.id).then(function() {

                    ngNotify.set($translate.instant('PASSWORD_GROUP_MANAGEMENT.TEXT.DELETE_COMPLETE'), 'success');

                    $rootScope.$broadcast('reloadPasswordTree');

                    if (typeof passwordGroup.parent != 'undefined' && passwordGroup.parent !== null && passwordGroup.parent.id !== null) {
                        $state.go('overview.group', {groupId: passwordGroup.parent.id});
                    } else {
                        $state.go('overview');
                    }
                }, function(res) {
                    if (res.status === 404) {
                        swal($translate.instant('TEXT.RESOURCE_NOT_FOUND'), $translate.instant('TEXT.DELETE_NOT_FOUND'), "error");
                    } if (res.status === 409) {
                        swal($translate.instant('PASSWORD_GROUP_MANAGEMENT.TEXT.DELETE_NOT_ALLOWED_TITLE'), $translate.instant('PASSWORD_GROUP_MANAGEMENT.TEXT.DELETE_NOT_ALLOWED'), "error");
                    } else {
                        swal($translate.instant('TEXT.UNKNOWN_ERROR'), $translate.instant('TEXT.UNKNOWN_ERROR_INFORMATION'), "error");
                    }
                });
            }, function(dismiss) {});
        };

    }]);

    app.controller('PasswordDetailController', ["$scope", "$translate", "$rootScope", "$state", "$stateParams", "$uibModal", "ngNotify", "PasswordManager", "PasswordGroupManager", function($scope, $translate, $rootScope, $state, $stateParams, $uibModal, ngNotify, PasswordManager, PasswordGroupManager) {

        var self = this;

        this.groupId = $stateParams.groupId;
        this.passwordId = $stateParams.passwordId;
        this.confirmingPassword = false;

        this.icons = ["fa-500px","fa-address-book","fa-address-book-o","fa-address-card","fa-address-card-o","fa-adjust","fa-adn","fa-align-center","fa-align-justify","fa-align-left","fa-align-right","fa-amazon","fa-ambulance","fa-american-sign-language-interpreting","fa-anchor","fa-android","fa-angellist","fa-angle-double-down","fa-angle-double-left","fa-angle-double-right","fa-angle-double-up","fa-angle-down","fa-angle-left","fa-angle-right","fa-angle-up","fa-apple","fa-archive","fa-area-chart","fa-arrow-circle-down","fa-arrow-circle-left","fa-arrow-circle-o-down","fa-arrow-circle-o-left","fa-arrow-circle-o-right","fa-arrow-circle-o-up","fa-arrow-circle-right","fa-arrow-circle-up","fa-arrow-down","fa-arrow-left","fa-arrow-right","fa-arrow-up","fa-arrows","fa-arrows-alt","fa-arrows-h","fa-arrows-v","fa-asl-interpreting","fa-assistive-listening-systems","fa-asterisk","fa-at","fa-audio-description","fa-automobile","fa-backward","fa-balance-scale","fa-ban","fa-bandcamp","fa-bank","fa-bar-chart","fa-bar-chart-o","fa-barcode","fa-bars","fa-bath","fa-bathtub","fa-battery","fa-battery-0","fa-battery-1","fa-battery-2","fa-battery-3","fa-battery-4","fa-battery-empty","fa-battery-full","fa-battery-half","fa-battery-quarter","fa-battery-three-quarters","fa-bed","fa-beer","fa-behance","fa-behance-square","fa-bell","fa-bell-o","fa-bell-slash","fa-bell-slash-o","fa-bicycle","fa-binoculars","fa-birthday-cake","fa-bitbucket","fa-bitbucket-square","fa-bitcoin","fa-black-tie","fa-blind","fa-bluetooth","fa-bluetooth-b","fa-bold","fa-bolt","fa-bomb","fa-book","fa-bookmark","fa-bookmark-o","fa-braille","fa-briefcase","fa-btc","fa-bug","fa-building","fa-building-o","fa-bullhorn","fa-bullseye","fa-bus","fa-buysellads","fa-cab","fa-calculator","fa-calendar","fa-calendar-check-o","fa-calendar-minus-o","fa-calendar-o","fa-calendar-plus-o","fa-calendar-times-o","fa-camera","fa-camera-retro","fa-car","fa-caret-down","fa-caret-left","fa-caret-right","fa-caret-square-o-down","fa-caret-square-o-left","fa-caret-square-o-right","fa-caret-square-o-up","fa-caret-up","fa-cart-arrow-down","fa-cart-plus","fa-cc","fa-cc-amex","fa-cc-diners-club","fa-cc-discover","fa-cc-jcb","fa-cc-mastercard","fa-cc-paypal","fa-cc-stripe","fa-cc-visa","fa-certificate","fa-chain","fa-chain-broken","fa-check","fa-check-circle","fa-check-circle-o","fa-check-square","fa-check-square-o","fa-chevron-circle-down","fa-chevron-circle-left","fa-chevron-circle-right","fa-chevron-circle-up","fa-chevron-down","fa-chevron-left","fa-chevron-right","fa-chevron-up","fa-child","fa-chrome","fa-circle","fa-circle-o","fa-circle-o-notch","fa-circle-thin","fa-clipboard","fa-clock-o","fa-clone","fa-close","fa-cloud","fa-cloud-download","fa-cloud-upload","fa-cny","fa-code","fa-code-fork","fa-codepen","fa-codiepie","fa-coffee","fa-cog","fa-cogs","fa-columns","fa-comment","fa-comment-o","fa-commenting","fa-commenting-o","fa-comments","fa-comments-o","fa-compass","fa-compress","fa-connectdevelop","fa-contao","fa-copy","fa-copyright","fa-creative-commons","fa-credit-card","fa-credit-card-alt","fa-crop","fa-crosshairs","fa-css3","fa-cube","fa-cubes","fa-cut","fa-cutlery","fa-dashboard","fa-dashcube","fa-database","fa-deaf","fa-deafness","fa-dedent","fa-delicious","fa-desktop","fa-deviantart","fa-diamond","fa-digg","fa-dollar","fa-dot-circle-o","fa-download","fa-dribbble","fa-drivers-license","fa-drivers-license-o","fa-dropbox","fa-drupal","fa-edge","fa-edit","fa-eercast","fa-eject","fa-ellipsis-h","fa-ellipsis-v","fa-empire","fa-envelope","fa-envelope-o","fa-envelope-open","fa-envelope-open-o","fa-envelope-square","fa-envira","fa-eraser","fa-etsy","fa-eur","fa-euro","fa-exchange","fa-exclamation","fa-exclamation-circle","fa-exclamation-triangle","fa-expand","fa-expeditedssl","fa-external-link","fa-external-link-square","fa-eye","fa-eye-slash","fa-eyedropper","fa-fa","fa-facebook","fa-facebook-f","fa-facebook-official","fa-facebook-square","fa-fast-backward","fa-fast-forward","fa-fax","fa-feed","fa-female","fa-fighter-jet","fa-file","fa-file-archive-o","fa-file-audio-o","fa-file-code-o","fa-file-excel-o","fa-file-image-o","fa-file-movie-o","fa-file-o","fa-file-pdf-o","fa-file-photo-o","fa-file-picture-o","fa-file-powerpoint-o","fa-file-sound-o","fa-file-text","fa-file-text-o","fa-file-video-o","fa-file-word-o","fa-file-zip-o","fa-files-o","fa-film","fa-filter","fa-fire","fa-fire-extinguisher","fa-firefox","fa-first-order","fa-flag","fa-flag-checkered","fa-flag-o","fa-flash","fa-flask","fa-flickr","fa-floppy-o","fa-folder","fa-folder-o","fa-folder-open","fa-folder-open-o","fa-font","fa-font-awesome","fa-fonticons","fa-fort-awesome","fa-forumbee","fa-forward","fa-foursquare","fa-free-code-camp","fa-frown-o","fa-futbol-o","fa-gamepad","fa-gavel","fa-gbp","fa-ge","fa-gear","fa-gears","fa-genderless","fa-get-pocket","fa-gg","fa-gg-circle","fa-gift","fa-git","fa-git-square","fa-github","fa-github-alt","fa-github-square","fa-gitlab","fa-gittip","fa-glass","fa-glide","fa-glide-g","fa-globe","fa-google","fa-google-plus","fa-google-plus-circle","fa-google-plus-official","fa-google-plus-square","fa-google-wallet","fa-graduation-cap","fa-gratipay","fa-grav","fa-group","fa-h-square","fa-hacker-news","fa-hand-grab-o","fa-hand-lizard-o","fa-hand-o-down","fa-hand-o-left","fa-hand-o-right","fa-hand-o-up","fa-hand-paper-o","fa-hand-peace-o","fa-hand-pointer-o","fa-hand-rock-o","fa-hand-scissors-o","fa-hand-spock-o","fa-hand-stop-o","fa-handshake-o","fa-hard-of-hearing","fa-hashtag","fa-hdd-o","fa-header","fa-headphones","fa-heart","fa-heart-o","fa-heartbeat","fa-history","fa-home","fa-hospital-o","fa-hotel","fa-hourglass","fa-hourglass-1","fa-hourglass-2","fa-hourglass-3","fa-hourglass-end","fa-hourglass-half","fa-hourglass-o","fa-hourglass-start","fa-houzz","fa-html5","fa-i-cursor","fa-id-badge","fa-id-card","fa-id-card-o","fa-ils","fa-image","fa-imdb","fa-inbox","fa-indent","fa-industry","fa-info","fa-info-circle","fa-inr","fa-instagram","fa-institution","fa-internet-explorer","fa-intersex","fa-ioxhost","fa-italic","fa-joomla","fa-jpy","fa-jsfiddle","fa-key","fa-keyboard-o","fa-krw","fa-language","fa-laptop","fa-lastfm","fa-lastfm-square","fa-leaf","fa-leanpub","fa-legal","fa-lemon-o","fa-level-down","fa-level-up","fa-life-bouy","fa-life-buoy","fa-life-ring","fa-life-saver","fa-lightbulb-o","fa-line-chart","fa-link","fa-linkedin","fa-linkedin-square","fa-linode","fa-linux","fa-list","fa-list-alt","fa-list-ol","fa-list-ul","fa-location-arrow","fa-lock","fa-long-arrow-down","fa-long-arrow-left","fa-long-arrow-right","fa-long-arrow-up","fa-low-vision","fa-magic","fa-magnet","fa-mail-forward","fa-mail-reply","fa-mail-reply-all","fa-male","fa-map","fa-map-marker","fa-map-o","fa-map-pin","fa-map-signs","fa-mars","fa-mars-double","fa-mars-stroke","fa-mars-stroke-h","fa-mars-stroke-v","fa-maxcdn","fa-meanpath","fa-medium","fa-medkit","fa-meetup","fa-meh-o","fa-mercury","fa-microchip","fa-microphone","fa-microphone-slash","fa-minus","fa-minus-circle","fa-minus-square","fa-minus-square-o","fa-mixcloud","fa-mobile","fa-mobile-phone","fa-modx","fa-money","fa-moon-o","fa-mortar-board","fa-motorcycle","fa-mouse-pointer","fa-music","fa-navicon","fa-neuter","fa-newspaper-o","fa-object-group","fa-object-ungroup","fa-odnoklassniki","fa-odnoklassniki-square","fa-opencart","fa-openid","fa-opera","fa-optin-monster","fa-outdent","fa-pagelines","fa-paint-brush","fa-paper-plane","fa-paper-plane-o","fa-paperclip","fa-paragraph","fa-paste","fa-pause","fa-pause-circle","fa-pause-circle-o","fa-paw","fa-paypal","fa-pencil","fa-pencil-square","fa-pencil-square-o","fa-percent","fa-phone","fa-phone-square","fa-photo","fa-picture-o","fa-pie-chart","fa-pied-piper","fa-pied-piper-alt","fa-pied-piper-pp","fa-pinterest","fa-pinterest-p","fa-pinterest-square","fa-plane","fa-play","fa-play-circle","fa-play-circle-o","fa-plug","fa-plus","fa-plus-circle","fa-plus-square","fa-plus-square-o","fa-podcast","fa-power-off","fa-print","fa-product-hunt","fa-puzzle-piece","fa-qq","fa-qrcode","fa-question","fa-question-circle","fa-question-circle-o","fa-quora","fa-quote-left","fa-quote-right","fa-ra","fa-random","fa-ravelry","fa-rebel","fa-recycle","fa-reddit","fa-reddit-alien","fa-reddit-square","fa-refresh","fa-registered","fa-remove","fa-renren","fa-reorder","fa-repeat","fa-reply","fa-reply-all","fa-resistance","fa-retweet","fa-rmb","fa-road","fa-rocket","fa-rotate-left","fa-rotate-right","fa-rouble","fa-rss","fa-rss-square","fa-rub","fa-ruble","fa-rupee","fa-s15","fa-safari","fa-save","fa-scissors","fa-scribd","fa-search","fa-search-minus","fa-search-plus","fa-sellsy","fa-send","fa-send-o","fa-server","fa-share","fa-share-alt","fa-share-alt-square","fa-share-square","fa-share-square-o","fa-shekel","fa-sheqel","fa-shield","fa-ship","fa-shirtsinbulk","fa-shopping-bag","fa-shopping-basket","fa-shopping-cart","fa-shower","fa-sign-in","fa-sign-language","fa-sign-out","fa-signal","fa-signing","fa-simplybuilt","fa-sitemap","fa-skyatlas","fa-skype","fa-slack","fa-sliders","fa-slideshare","fa-smile-o","fa-snapchat","fa-snapchat-ghost","fa-snapchat-square","fa-snowflake-o","fa-soccer-ball-o","fa-sort","fa-sort-alpha-asc","fa-sort-alpha-desc","fa-sort-amount-asc","fa-sort-amount-desc","fa-sort-asc","fa-sort-desc","fa-sort-down","fa-sort-numeric-asc","fa-sort-numeric-desc","fa-sort-up","fa-soundcloud","fa-space-shuttle","fa-spinner","fa-spoon","fa-spotify","fa-square","fa-square-o","fa-stack-exchange","fa-stack-overflow","fa-star","fa-star-half","fa-star-half-empty","fa-star-half-full","fa-star-half-o","fa-star-o","fa-steam","fa-steam-square","fa-step-backward","fa-step-forward","fa-stethoscope","fa-sticky-note","fa-sticky-note-o","fa-stop","fa-stop-circle","fa-stop-circle-o","fa-street-view","fa-strikethrough","fa-stumbleupon","fa-stumbleupon-circle","fa-subscript","fa-subway","fa-suitcase","fa-sun-o","fa-superpowers","fa-superscript","fa-support","fa-table","fa-tablet","fa-tachometer","fa-tag","fa-tags","fa-tasks","fa-taxi","fa-telegram","fa-television","fa-tencent-weibo","fa-terminal","fa-text-height","fa-text-width","fa-th","fa-th-large","fa-th-list","fa-themeisle","fa-thermometer","fa-thermometer-0","fa-thermometer-1","fa-thermometer-2","fa-thermometer-3","fa-thermometer-4","fa-thermometer-empty","fa-thermometer-full","fa-thermometer-half","fa-thermometer-quarter","fa-thermometer-three-quarters","fa-thumb-tack","fa-thumbs-down","fa-thumbs-o-down","fa-thumbs-o-up","fa-thumbs-up","fa-ticket","fa-times","fa-times-circle","fa-times-circle-o","fa-times-rectangle","fa-times-rectangle-o","fa-tint","fa-toggle-down","fa-toggle-left","fa-toggle-off","fa-toggle-on","fa-toggle-right","fa-toggle-up","fa-trademark","fa-train","fa-transgender","fa-transgender-alt","fa-trash","fa-trash-o","fa-tree","fa-trello","fa-tripadvisor","fa-trophy","fa-truck","fa-try","fa-tty","fa-tumblr","fa-tumblr-square","fa-turkish-lira","fa-tv","fa-twitch","fa-twitter","fa-twitter-square","fa-umbrella","fa-underline","fa-undo","fa-universal-access","fa-university","fa-unlink","fa-unlock","fa-unlock-alt","fa-unsorted","fa-upload","fa-usb","fa-usd","fa-user","fa-user-circle","fa-user-circle-o","fa-user-md","fa-user-o","fa-user-plus","fa-user-secret","fa-user-times","fa-users","fa-vcard","fa-vcard-o","fa-venus","fa-venus-double","fa-venus-mars","fa-viacoin","fa-viadeo","fa-viadeo-square","fa-video-camera","fa-vimeo","fa-vimeo-square","fa-vine","fa-vk","fa-volume-control-phone","fa-volume-down","fa-volume-off","fa-volume-up","fa-warning","fa-wechat","fa-weibo","fa-weixin","fa-whatsapp","fa-wheelchair","fa-wheelchair-alt","fa-wifi","fa-wikipedia-w","fa-window-close","fa-window-close-o","fa-window-maximize","fa-window-minimize","fa-window-restore","fa-windows","fa-won","fa-wordpress","fa-wpbeginner","fa-wpexplorer","fa-wpforms","fa-wrench","fa-xing","fa-xing-square","fa-y-combinator","fa-y-combinator-square","fa-yahoo","fa-yc","fa-yc-square","fa-yelp","fa-yen","fa-yoast","fa-youtube","fa-youtube-play","fa-youtube-square"];

        this.loaded = 2;

        this.radio = {
            model: null
        };

        this.addShareLink = function() {
            var shareLinkModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/share_link.html?v=2",
                controller: 'ModalShareLinkController',
                backdrop: 'static',
                size: "lg"
            });

            shareLinkModal.result.then(function (response) {}, function() {});
        };

        this.confirmPassword = function() {
            self.confirmingPassword = true;
            PasswordManager.postPasswordConfirm($stateParams.passwordId).then(function (data) {
                self.loadPassword();
            });
        };

        this.loadPassword = function() {
            PasswordManager.getPassword($stateParams.passwordId).then(function (data) {
                self.confirmingPassword = false;
                self.radio.model = data.icon;
                $scope.password = data;
                self.loaded -= 1;
            });
        };

        this.movePassword = function() {
            var movePasswordModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/move_password.html",
                controller: 'ModalMovePasswordController',
                size: "mg",
                backdrop: 'static',
                resolve: {
                    password: function () {
                        return $scope.password;
                    }
                }
            });

            movePasswordModal.result.then(function (response) {
                $state.go('overview.group', {groupId: response});
            }, function() {});
        };

        $rootScope.$on('passwordChanged', function(event, mass) {
            self.loadPassword();
        });

        this.loadPassword();

        this.updateingIcon = false;
        this.iconErrors = {};


        this.successIcon = false;
        this.submitIcon = function() {
            if (!self.updateingIcon) {

                self.successIcon = false;
                self.iconErrors = {};

                self.updateingIcon = true;

                PasswordManager.putPassword($stateParams.passwordId, {icon: self.radio.model}).then(function(response) {
                    self.updateingIcon = false;
                    self.radio.model = response.icon;
                    self.successIcon = true;
                    $scope.password = response;
                }, function(res) {
                    if (res.status == 400 && res.data.errors !== undefined) {
                        self.iconErrors = ApiFormatManager.formatApiFormErrors(res.data.errors.children);
                    }

                    self.updateingIcon = false;
                });
            }

        };

        this.delete = function() {
            swal({
                title: $translate.instant('TEXT.ARE_YOU_SURE'),
                text: $translate.instant('PASSWORD_MANAGEMENT.TEXT.DELETE_INFORMATION'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $translate.instant('WORDS.YES'),
                cancelButtonText: $translate.instant('WORDS.CLOSE')
            }).then(function() {
                PasswordManager.deletePassword($stateParams.passwordId).then(function() {

                    ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.TEXT.DELETE_COMPLETE'), 'success');

                    $state.go('overview.group', {groupId: $stateParams.groupId});

                    $rootScope.$broadcast('reloadPasswordOverview', []);

                }, function(res) {
                    if (res.status === 404) {
                        swal($translate.instant('TEXT.RESOURCE_NOT_FOUND'), $translate.instant('TEXT.DELETE_NOT_FOUND'), "error");
                    } else {
                        swal($translate.instant('TEXT.UNKNOWN_ERROR'), $translate.instant('TEXT.UNKNOWN_ERROR_INFORMATION'), "error");
                    }
                });
            }, function(dismiss) {});
        };

        this.onCopied = function(entity) {
            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.ENTRY_COPIED', { entry: $translate.instant(entity) }), 'success');
        };

        this.onCopyFailed = function(err, entry, text) {
            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.COPY_FAILED'), 'error');
            swal($translate.instant(entry), text, "info");
        };

        PasswordGroupManager.getPasswordGroup($stateParams.groupId).then(function (data) {
            $scope.passwordGroup = data;
            self.loaded -= 1;
        });

        $scope.passwordInputType = 'password';

        $scope.togglePasswordVisibility = function(){
            if ($scope.passwordInputType == 'password') {
                $scope.passwordInputType = 'text';
            } else {
                $scope.passwordInputType = 'password';
            }
        };
    }]);

    app.controller('PasswordOverviewController', ["$rootScope", "$scope", "$translate", "$stateParams", "ngNotify", "NgTableParams", "PasswordManager", "PasswordGroupManager", "ApiFormatManager", function($rootScope, $scope, $translate, $stateParams, ngNotify, NgTableParams, PasswordManager, PasswordGroupManager, ApiFormatManager) {
        
        var self = this;

        $scope.loadingPasswordGroup = true;
        $scope.loadingPasswords = true;
        $scope.accessDenied = false;

        $scope.breadcrumbs = [];
        
        this.addToBreadcrumb = function(passwordGroup) {

            $scope.breadcrumbs.unshift({
                id: passwordGroup.id,
                name: passwordGroup.name
            });
            
            if (passwordGroup.parent !== null) {
                self.addToBreadcrumb(passwordGroup.parent);
            }
        };

        $scope.passwordGroup = null;

        PasswordGroupManager.getPasswordGroup($stateParams.groupId).then(function (data) {
            $scope.passwordGroup = data;
            
            self.addToBreadcrumb(data);

            $scope.loadingPasswordGroup = false;
        }, function(res) {
            if (res.status == 403) {
                $scope.accessDenied = true;
            }
        });


        this.reloadData = function() {
            $scope.tableParams = new NgTableParams({
                page: 1,
                count: 10
            }, {
                total: 0,
                getData: function (params) {
                    return PasswordGroupManager.getPasswordsByPasswordGroup($stateParams.groupId, ApiFormatManager.formatNGTableParametersToRest(params)).then(function (data) {
                        $scope.loadingPasswords = false;
                        params.total(data.total);
                        return data;
                    });
                }
            });
        };

        this.reloadData();

        $rootScope.$on('reloadPasswordOverview', function(event, mass) {
            $scope.loadingPasswords = true;
            self.reloadData();
        });

        this.onPasswordCopied = function() {
            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.PASSWORD_COPIED'), 'success');
        };

        this.onPasswordCopyFailed = function(err, password) {
            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.PASSWORD_COPY_FAILED'), 'error');
            swal($translate.instant('WORDS.PASSWORD'), password.password, "info");
        };

        this.onPinCopied = function() {
            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.PIN_COPIED'), 'success');
        };

        this.onPinCopyFailed = function(err, password) {
            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.PIN_COPY_FAILED'), 'error');
            swal($translate.instant('WORDS.PIN'), password.pin, "info");
        };


        this.onUsernameCopied = function() {
            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.USERNAME_COPIED'), 'success');
        };

        this.onUsernameCopyFailed = function(err) {
            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.USERNAME_COPY_FAILED'), 'error');
            swal($translate.instant('WORDS.USERNAME'), password.username, "info");
        };

        this.delete = function(password) {
            swal({
                title: $translate.instant('TEXT.ARE_YOU_SURE'),
                text: $translate.instant('PASSWORD_MANAGEMENT.TEXT.DELETE_INFORMATION'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $translate.instant('WORDS.YES'),
                cancelButtonText: $translate.instant('WORDS.CLOSE')
            }).then(function() {
                PasswordManager.deletePassword(password.id).then(function() {
                    self.reloadData();

                    ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.TEXT.DELETE_COMPLETE'), 'success');

                }, function(res) {
                    if (res.status == 404) {
                        swal($translate.instant('TEXT.RESOURCE_NOT_FOUND'), $translate.instant('TEXT.DELETE_NOT_FOUND'), "error");
                    } else {
                        swal($translate.instant('TEXT.UNKNOWN_ERROR'), $translate.instant('TEXT.UNKNOWN_ERROR_INFORMATION'), "error");
                    }
                });
            }, function(dismiss) {});
        };

        $scope.supported = false;
    }]);


    app.controller('ModalShareLinkController', ["$rootScope", "$scope", "$uibModalInstance", "$translate", "$stateParams", "ngNotify", "ApiFormatManager", "PasswordManager", function($rootScope, $scope, $uibModalInstance, $translate, $stateParams, ngNotify, ApiFormatManager, PasswordManager) {

        $scope.isLaddaWorking = false;

        $scope.formErrors = {};
        $scope.data = {
            mode: "1",
            recipient: "",
            valid_to: "",
            viewLimit: ''
        };

        $scope.editingMode = false;

        $scope.loaded = false;

        var self = this;

        $scope.submit = function() {

            $scope.$broadcast('show-errors-check-validity');

            if (!$scope.isLaddaWorking && $scope.modalForm.$valid) {

                $scope.formErrors = {};

                $scope.isLaddaWorking = true;

                self.submitSuccessResponseAdded = function (response) {
                    $uibModalInstance.close(response);
                    ngNotify.set($translate.instant('SHARE_LINK.CREATED_SUCCESSFULLY'), 'success');

                    $rootScope.$broadcast('shareLinkChange');
                };

                self.submitErrorResponse = function (res) {
                    if (res.status == 400 && res.data.errors !== undefined) {
                        $scope.modalForm.$setUntouched();
                        $scope.modalForm.$setPristine();
                        $scope.$broadcast('show-errors-reset');

                        $scope.formErrors = ApiFormatManager.formatApiFormErrors(res.data.errors.children);
                    }

                    $scope.isLaddaWorking = false;
                };

                PasswordManager.postPasswordShares($stateParams.passwordId, $scope.data).then(self.submitSuccessResponseAdded, self.submitErrorResponse);
            }
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    }]);


    app.controller('ModalAddOrEditPasswordController', ["$scope", "$uibModalInstance", "$translate", "ngNotify", "ApiFormatManager", "UserManager", "PasswordManager", "PasswordGroupManager", "password", "passwordGroup", function($scope, $uibModalInstance, $translate, ngNotify, ApiFormatManager, UserManager, PasswordManager, PasswordGroupManager, password, passwordGroup) {

        $scope.isLaddaWorking = false;

        $scope.formErrors = {};

        $scope.editingMode = false;

        $scope.passwordInputType = 'password';

        $scope.passwordType = 'plain';

        $scope.passwordOptions = {
            passwordLength: 16,
            addUpper: true,
            addNumbers: true,
            addSymbols: false
        };

        $scope.loaded = false;

        var self = this;

        if (password !== null) {

            PasswordManager.getPassword(password.id).then(function (data) {
                $scope.data = data;
                $scope.loaded = true;
            });

            $scope.editingMode = true;

        } else {
            $scope.data = {
                name: '',
                url: '',
                username: '',
                notice: '',
                custom_fields: []
            };
            $scope.loaded = true;
        }

        $scope.togglePasswordVisibility = function(){
            if ($scope.passwordInputType == 'password') {
                $scope.passwordInputType = 'text';
            } else {
                $scope.passwordInputType = 'password';
            }
        };

        $scope.submit = function() {

            $scope.$broadcast('show-errors-check-validity');

            if (!$scope.isLaddaWorking && $scope.modalForm.$valid) {

                $scope.formErrors = {};

                $scope.isLaddaWorking = true;

                self.submitSuccessResponseAdd = function(response) {
                    $uibModalInstance.close(response);
                    ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.TEXT.PASSWORD_SUCCESSFULLY_ADDED'), 'success');
                };

                self.submitSuccessResponseEdit = function(response) {
                    $uibModalInstance.close(response);
                    ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.TEXT.PASSWORD_SUCCESSFULLY_UPDATED'), 'success');
                };

                self.submitErrorResponse = function(res) {
                    if (res.status === 400 && res.data.errors !== undefined) {
                        $scope.modalForm.$setUntouched();
                        $scope.modalForm.$setPristine();
                        $scope.$broadcast('show-errors-reset');

                        $scope.formErrors = ApiFormatManager.formatApiFormErrors(res.data.errors.children);
                    }

                    $scope.isLaddaWorking = false;
                };

                if ($scope.editingMode) {
                    PasswordManager.putPassword(password.id, $scope.data).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                } else {
                    PasswordGroupManager.postPlainPassword(passwordGroup.id, $scope.data).then(self.submitSuccessResponseAdd, self.submitErrorResponse);
                }


            }

        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    }]);

    app.controller('ModalAddOrEditPasswordGroupController', ["$rootScope", "$scope", "$uibModalInstance", "ApiFormatManager", "UserManager", "PasswordManager", "PasswordGroupManager", "passwordGroup", function($rootScope, $scope, $uibModalInstance, ApiFormatManager, UserManager, PasswordManager, PasswordGroupManager, passwordGroup) {

        $scope.isLaddaWorking = false;

        $scope.formErrors = {};

        $scope.editingMode = false;

        var self = this;

        $scope.radio = {
            model: null
        };

        console.log('test');
        console.log(passwordGroup);

        if (passwordGroup !== null) {
            $scope.data = passwordGroup;
            $scope.editingMode = true;

            $scope.radio.model = passwordGroup.icon;

        } else {
            $scope.data = {
                name: ''
            };
        }

        $scope.icons = ["fa-500px","fa-address-book","fa-address-book-o","fa-address-card","fa-address-card-o","fa-adjust","fa-adn","fa-align-center","fa-align-justify","fa-align-left","fa-align-right","fa-amazon","fa-ambulance","fa-american-sign-language-interpreting","fa-anchor","fa-android","fa-angellist","fa-angle-double-down","fa-angle-double-left","fa-angle-double-right","fa-angle-double-up","fa-angle-down","fa-angle-left","fa-angle-right","fa-angle-up","fa-apple","fa-archive","fa-area-chart","fa-arrow-circle-down","fa-arrow-circle-left","fa-arrow-circle-o-down","fa-arrow-circle-o-left","fa-arrow-circle-o-right","fa-arrow-circle-o-up","fa-arrow-circle-right","fa-arrow-circle-up","fa-arrow-down","fa-arrow-left","fa-arrow-right","fa-arrow-up","fa-arrows","fa-arrows-alt","fa-arrows-h","fa-arrows-v","fa-asl-interpreting","fa-assistive-listening-systems","fa-asterisk","fa-at","fa-audio-description","fa-automobile","fa-backward","fa-balance-scale","fa-ban","fa-bandcamp","fa-bank","fa-bar-chart","fa-bar-chart-o","fa-barcode","fa-bars","fa-bath","fa-bathtub","fa-battery","fa-battery-0","fa-battery-1","fa-battery-2","fa-battery-3","fa-battery-4","fa-battery-empty","fa-battery-full","fa-battery-half","fa-battery-quarter","fa-battery-three-quarters","fa-bed","fa-beer","fa-behance","fa-behance-square","fa-bell","fa-bell-o","fa-bell-slash","fa-bell-slash-o","fa-bicycle","fa-binoculars","fa-birthday-cake","fa-bitbucket","fa-bitbucket-square","fa-bitcoin","fa-black-tie","fa-blind","fa-bluetooth","fa-bluetooth-b","fa-bold","fa-bolt","fa-bomb","fa-book","fa-bookmark","fa-bookmark-o","fa-braille","fa-briefcase","fa-btc","fa-bug","fa-building","fa-building-o","fa-bullhorn","fa-bullseye","fa-bus","fa-buysellads","fa-cab","fa-calculator","fa-calendar","fa-calendar-check-o","fa-calendar-minus-o","fa-calendar-o","fa-calendar-plus-o","fa-calendar-times-o","fa-camera","fa-camera-retro","fa-car","fa-caret-down","fa-caret-left","fa-caret-right","fa-caret-square-o-down","fa-caret-square-o-left","fa-caret-square-o-right","fa-caret-square-o-up","fa-caret-up","fa-cart-arrow-down","fa-cart-plus","fa-cc","fa-cc-amex","fa-cc-diners-club","fa-cc-discover","fa-cc-jcb","fa-cc-mastercard","fa-cc-paypal","fa-cc-stripe","fa-cc-visa","fa-certificate","fa-chain","fa-chain-broken","fa-check","fa-check-circle","fa-check-circle-o","fa-check-square","fa-check-square-o","fa-chevron-circle-down","fa-chevron-circle-left","fa-chevron-circle-right","fa-chevron-circle-up","fa-chevron-down","fa-chevron-left","fa-chevron-right","fa-chevron-up","fa-child","fa-chrome","fa-circle","fa-circle-o","fa-circle-o-notch","fa-circle-thin","fa-clipboard","fa-clock-o","fa-clone","fa-close","fa-cloud","fa-cloud-download","fa-cloud-upload","fa-cny","fa-code","fa-code-fork","fa-codepen","fa-codiepie","fa-coffee","fa-cog","fa-cogs","fa-columns","fa-comment","fa-comment-o","fa-commenting","fa-commenting-o","fa-comments","fa-comments-o","fa-compass","fa-compress","fa-connectdevelop","fa-contao","fa-copy","fa-copyright","fa-creative-commons","fa-credit-card","fa-credit-card-alt","fa-crop","fa-crosshairs","fa-css3","fa-cube","fa-cubes","fa-cut","fa-cutlery","fa-dashboard","fa-dashcube","fa-database","fa-deaf","fa-deafness","fa-dedent","fa-delicious","fa-desktop","fa-deviantart","fa-diamond","fa-digg","fa-dollar","fa-dot-circle-o","fa-download","fa-dribbble","fa-drivers-license","fa-drivers-license-o","fa-dropbox","fa-drupal","fa-edge","fa-edit","fa-eercast","fa-eject","fa-ellipsis-h","fa-ellipsis-v","fa-empire","fa-envelope","fa-envelope-o","fa-envelope-open","fa-envelope-open-o","fa-envelope-square","fa-envira","fa-eraser","fa-etsy","fa-eur","fa-euro","fa-exchange","fa-exclamation","fa-exclamation-circle","fa-exclamation-triangle","fa-expand","fa-expeditedssl","fa-external-link","fa-external-link-square","fa-eye","fa-eye-slash","fa-eyedropper","fa-fa","fa-facebook","fa-facebook-f","fa-facebook-official","fa-facebook-square","fa-fast-backward","fa-fast-forward","fa-fax","fa-feed","fa-female","fa-fighter-jet","fa-file","fa-file-archive-o","fa-file-audio-o","fa-file-code-o","fa-file-excel-o","fa-file-image-o","fa-file-movie-o","fa-file-o","fa-file-pdf-o","fa-file-photo-o","fa-file-picture-o","fa-file-powerpoint-o","fa-file-sound-o","fa-file-text","fa-file-text-o","fa-file-video-o","fa-file-word-o","fa-file-zip-o","fa-files-o","fa-film","fa-filter","fa-fire","fa-fire-extinguisher","fa-firefox","fa-first-order","fa-flag","fa-flag-checkered","fa-flag-o","fa-flash","fa-flask","fa-flickr","fa-floppy-o","fa-folder","fa-folder-o","fa-folder-open","fa-folder-open-o","fa-font","fa-font-awesome","fa-fonticons","fa-fort-awesome","fa-forumbee","fa-forward","fa-foursquare","fa-free-code-camp","fa-frown-o","fa-futbol-o","fa-gamepad","fa-gavel","fa-gbp","fa-ge","fa-gear","fa-gears","fa-genderless","fa-get-pocket","fa-gg","fa-gg-circle","fa-gift","fa-git","fa-git-square","fa-github","fa-github-alt","fa-github-square","fa-gitlab","fa-gittip","fa-glass","fa-glide","fa-glide-g","fa-globe","fa-google","fa-google-plus","fa-google-plus-circle","fa-google-plus-official","fa-google-plus-square","fa-google-wallet","fa-graduation-cap","fa-gratipay","fa-grav","fa-group","fa-h-square","fa-hacker-news","fa-hand-grab-o","fa-hand-lizard-o","fa-hand-o-down","fa-hand-o-left","fa-hand-o-right","fa-hand-o-up","fa-hand-paper-o","fa-hand-peace-o","fa-hand-pointer-o","fa-hand-rock-o","fa-hand-scissors-o","fa-hand-spock-o","fa-hand-stop-o","fa-handshake-o","fa-hard-of-hearing","fa-hashtag","fa-hdd-o","fa-header","fa-headphones","fa-heart","fa-heart-o","fa-heartbeat","fa-history","fa-home","fa-hospital-o","fa-hotel","fa-hourglass","fa-hourglass-1","fa-hourglass-2","fa-hourglass-3","fa-hourglass-end","fa-hourglass-half","fa-hourglass-o","fa-hourglass-start","fa-houzz","fa-html5","fa-i-cursor","fa-id-badge","fa-id-card","fa-id-card-o","fa-ils","fa-image","fa-imdb","fa-inbox","fa-indent","fa-industry","fa-info","fa-info-circle","fa-inr","fa-instagram","fa-institution","fa-internet-explorer","fa-intersex","fa-ioxhost","fa-italic","fa-joomla","fa-jpy","fa-jsfiddle","fa-key","fa-keyboard-o","fa-krw","fa-language","fa-laptop","fa-lastfm","fa-lastfm-square","fa-leaf","fa-leanpub","fa-legal","fa-lemon-o","fa-level-down","fa-level-up","fa-life-bouy","fa-life-buoy","fa-life-ring","fa-life-saver","fa-lightbulb-o","fa-line-chart","fa-link","fa-linkedin","fa-linkedin-square","fa-linode","fa-linux","fa-list","fa-list-alt","fa-list-ol","fa-list-ul","fa-location-arrow","fa-lock","fa-long-arrow-down","fa-long-arrow-left","fa-long-arrow-right","fa-long-arrow-up","fa-low-vision","fa-magic","fa-magnet","fa-mail-forward","fa-mail-reply","fa-mail-reply-all","fa-male","fa-map","fa-map-marker","fa-map-o","fa-map-pin","fa-map-signs","fa-mars","fa-mars-double","fa-mars-stroke","fa-mars-stroke-h","fa-mars-stroke-v","fa-maxcdn","fa-meanpath","fa-medium","fa-medkit","fa-meetup","fa-meh-o","fa-mercury","fa-microchip","fa-microphone","fa-microphone-slash","fa-minus","fa-minus-circle","fa-minus-square","fa-minus-square-o","fa-mixcloud","fa-mobile","fa-mobile-phone","fa-modx","fa-money","fa-moon-o","fa-mortar-board","fa-motorcycle","fa-mouse-pointer","fa-music","fa-navicon","fa-neuter","fa-newspaper-o","fa-object-group","fa-object-ungroup","fa-odnoklassniki","fa-odnoklassniki-square","fa-opencart","fa-openid","fa-opera","fa-optin-monster","fa-outdent","fa-pagelines","fa-paint-brush","fa-paper-plane","fa-paper-plane-o","fa-paperclip","fa-paragraph","fa-paste","fa-pause","fa-pause-circle","fa-pause-circle-o","fa-paw","fa-paypal","fa-pencil","fa-pencil-square","fa-pencil-square-o","fa-percent","fa-phone","fa-phone-square","fa-photo","fa-picture-o","fa-pie-chart","fa-pied-piper","fa-pied-piper-alt","fa-pied-piper-pp","fa-pinterest","fa-pinterest-p","fa-pinterest-square","fa-plane","fa-play","fa-play-circle","fa-play-circle-o","fa-plug","fa-plus","fa-plus-circle","fa-plus-square","fa-plus-square-o","fa-podcast","fa-power-off","fa-print","fa-product-hunt","fa-puzzle-piece","fa-qq","fa-qrcode","fa-question","fa-question-circle","fa-question-circle-o","fa-quora","fa-quote-left","fa-quote-right","fa-ra","fa-random","fa-ravelry","fa-rebel","fa-recycle","fa-reddit","fa-reddit-alien","fa-reddit-square","fa-refresh","fa-registered","fa-remove","fa-renren","fa-reorder","fa-repeat","fa-reply","fa-reply-all","fa-resistance","fa-retweet","fa-rmb","fa-road","fa-rocket","fa-rotate-left","fa-rotate-right","fa-rouble","fa-rss","fa-rss-square","fa-rub","fa-ruble","fa-rupee","fa-s15","fa-safari","fa-save","fa-scissors","fa-scribd","fa-search","fa-search-minus","fa-search-plus","fa-sellsy","fa-send","fa-send-o","fa-server","fa-share","fa-share-alt","fa-share-alt-square","fa-share-square","fa-share-square-o","fa-shekel","fa-sheqel","fa-shield","fa-ship","fa-shirtsinbulk","fa-shopping-bag","fa-shopping-basket","fa-shopping-cart","fa-shower","fa-sign-in","fa-sign-language","fa-sign-out","fa-signal","fa-signing","fa-simplybuilt","fa-sitemap","fa-skyatlas","fa-skype","fa-slack","fa-sliders","fa-slideshare","fa-smile-o","fa-snapchat","fa-snapchat-ghost","fa-snapchat-square","fa-snowflake-o","fa-soccer-ball-o","fa-sort","fa-sort-alpha-asc","fa-sort-alpha-desc","fa-sort-amount-asc","fa-sort-amount-desc","fa-sort-asc","fa-sort-desc","fa-sort-down","fa-sort-numeric-asc","fa-sort-numeric-desc","fa-sort-up","fa-soundcloud","fa-space-shuttle","fa-spinner","fa-spoon","fa-spotify","fa-square","fa-square-o","fa-stack-exchange","fa-stack-overflow","fa-star","fa-star-half","fa-star-half-empty","fa-star-half-full","fa-star-half-o","fa-star-o","fa-steam","fa-steam-square","fa-step-backward","fa-step-forward","fa-stethoscope","fa-sticky-note","fa-sticky-note-o","fa-stop","fa-stop-circle","fa-stop-circle-o","fa-street-view","fa-strikethrough","fa-stumbleupon","fa-stumbleupon-circle","fa-subscript","fa-subway","fa-suitcase","fa-sun-o","fa-superpowers","fa-superscript","fa-support","fa-table","fa-tablet","fa-tachometer","fa-tag","fa-tags","fa-tasks","fa-taxi","fa-telegram","fa-television","fa-tencent-weibo","fa-terminal","fa-text-height","fa-text-width","fa-th","fa-th-large","fa-th-list","fa-themeisle","fa-thermometer","fa-thermometer-0","fa-thermometer-1","fa-thermometer-2","fa-thermometer-3","fa-thermometer-4","fa-thermometer-empty","fa-thermometer-full","fa-thermometer-half","fa-thermometer-quarter","fa-thermometer-three-quarters","fa-thumb-tack","fa-thumbs-down","fa-thumbs-o-down","fa-thumbs-o-up","fa-thumbs-up","fa-ticket","fa-times","fa-times-circle","fa-times-circle-o","fa-times-rectangle","fa-times-rectangle-o","fa-tint","fa-toggle-down","fa-toggle-left","fa-toggle-off","fa-toggle-on","fa-toggle-right","fa-toggle-up","fa-trademark","fa-train","fa-transgender","fa-transgender-alt","fa-trash","fa-trash-o","fa-tree","fa-trello","fa-tripadvisor","fa-trophy","fa-truck","fa-try","fa-tty","fa-tumblr","fa-tumblr-square","fa-turkish-lira","fa-tv","fa-twitch","fa-twitter","fa-twitter-square","fa-umbrella","fa-underline","fa-undo","fa-universal-access","fa-university","fa-unlink","fa-unlock","fa-unlock-alt","fa-unsorted","fa-upload","fa-usb","fa-usd","fa-user","fa-user-circle","fa-user-circle-o","fa-user-md","fa-user-o","fa-user-plus","fa-user-secret","fa-user-times","fa-users","fa-vcard","fa-vcard-o","fa-venus","fa-venus-double","fa-venus-mars","fa-viacoin","fa-viadeo","fa-viadeo-square","fa-video-camera","fa-vimeo","fa-vimeo-square","fa-vine","fa-vk","fa-volume-control-phone","fa-volume-down","fa-volume-off","fa-volume-up","fa-warning","fa-wechat","fa-weibo","fa-weixin","fa-whatsapp","fa-wheelchair","fa-wheelchair-alt","fa-wifi","fa-wikipedia-w","fa-window-close","fa-window-close-o","fa-window-maximize","fa-window-minimize","fa-window-restore","fa-windows","fa-won","fa-wordpress","fa-wpbeginner","fa-wpexplorer","fa-wpforms","fa-wrench","fa-xing","fa-xing-square","fa-y-combinator","fa-y-combinator-square","fa-yahoo","fa-yc","fa-yc-square","fa-yelp","fa-yen","fa-yoast","fa-youtube","fa-youtube-play","fa-youtube-square"];

        $scope.submit = function() {

            $scope.$broadcast('show-errors-check-validity');

            if (!$scope.isLaddaWorking && $scope.modalForm.$valid) {

                $scope.formErrors = {};

                $scope.isLaddaWorking = true;

                self.submitSuccessResponse = function(response) {
                    $rootScope.$broadcast('reloadPasswordTree');
                    $uibModalInstance.close(response);
                };

                self.submitErrorResponse = function(res) {
                    if (res.status == 400 && res.data.errors !== undefined) {
                        $scope.modalForm.$setUntouched();
                        $scope.modalForm.$setPristine();
                        $scope.$broadcast('show-errors-reset');

                        $scope.formErrors = ApiFormatManager.formatApiFormErrors(res.data.errors.children);
                    }

                    $scope.isLaddaWorking = false;
                };

                if ($scope.editingMode) {
                    PasswordGroupManager.putPasswordGroup(passwordGroup.id, $scope.data.name, $scope.radio.model).then(self.submitSuccessResponse, self.submitErrorResponse);
                } else {
                    PasswordGroupManager.postPasswordGroup($scope.data.name, $scope.radio.model, null).then(self.submitSuccessResponse, self.submitErrorResponse);
                }


            }

        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    }]);

    app.controller('EditAccessPasswordController', ["MY_GLOBAL_SETTINGS", "$scope", "$stateParams", "NgTableParams", "ApiFormatManager", "UserManager", "PasswordManager", "PasswordGroupManager", function(MY_GLOBAL_SETTINGS, $scope, $stateParams, NgTableParams, ApiFormatManager, UserManager, PasswordManager, PasswordGroupManager) {

        var self = this;

        PasswordManager.getPassword($stateParams.passwordId).then(function (data) {
            $scope.data = data;
        });

        PasswordGroupManager.getPasswordGroup($stateParams.groupId).then(function (data) {
            $scope.passwordGroup = data;
        }, function(res) {
        });


        $scope.getUsers = function(val) {
            var isnum = /^\d+$/.test(val);
            var query = "where id = '" + val.replace(/'/g, "\\'") + "' OR full_name LIKE '%" + val.replace(/'/g, "\\'") + "%' OR email LIKE '%" + val.replace(/'/g, "\\'") + "%'";

            if (!isnum) {
                query = "where full_name LIKE '%" + val.replace(/'/g, "\\'") + "%' OR email LIKE '%" + val.replace(/'/g, "\\'") + "%'";
            }

            return UserManager.getUsers({
                'query': query
            }).then(function(response){
                return response;
            });
        };

        $scope.user = null;

        $scope.loaded = false;

        $scope.showAlreadyAddedError = false;
        $scope.showOwnUserError = false;

        $scope.currentUser = MY_GLOBAL_SETTINGS.user;

        $scope.typeaheadOnSelect = function($item, $model, $label, $event) {

            PasswordManager.postPasswordAccess($stateParams.passwordId, $item.id, 1).then(function (data) {
                self.reloadData();
                $scope.user = null;
            }, function(res) {
                if (res.status == 400 && res.data.errors !== undefined) {
                    $scope.user = null;
                }

                if (res.status == 400 && res.data.errors !== undefined && res.data.errors.children.user.errors[0] == "User has already password right.") {
                    $scope.showAlreadyAddedError = true;
                }
            });

        };

        $scope.selectRights = [
            { id: '', title: 'All'},
            { id: 1, title: 'Read-Only'},
            { id: 2, title: 'Moderator'},
            { id: 3, title: 'Administrator'}
        ];

        $scope.delete = function(passwordAccess) {
            $scope.showOwnUserError = false;

            PasswordManager.deletePasswordAccess($stateParams.passwordId, passwordAccess.id).then(function (data) {
                self.reloadData();
            }, function(res) {
                if (res.status === 409) {
                    $scope.showOwnUserError = true;
                    self.reloadData();
                }
            });
        };

        $scope.updateAccess = function(passwordAccess, right) {
            $scope.showOwnUserError = false;

            PasswordManager.putPasswordAccess($stateParams.passwordId, passwordAccess.id, right).then(function (data) {
                self.reloadData();
            }, function(res) {
                if (res.status === 409) {
                    $scope.showOwnUserError = true;
                    self.reloadData();
                }
            });
        };

        self.reloadData = function() {
            $scope.tableParams = new NgTableParams({
                page: 1,
                count: 10,
                filter: {'right': ""}
            }, {
                total: 0,
                getData: function (params) {
                    return PasswordManager.getPasswordAccesses($stateParams.passwordId, ApiFormatManager.formatNGTableParametersToRest(params)).then(function (data) {
                        $scope.loadingPasswords = false;
                        params.total(data.total);
                        $scope.loaded = true;
                        return data;
                    });
                }
            });
        };

        self.reloadData();
    }]);

    app.controller('EditGroupAccessPasswordController', ["MY_GLOBAL_SETTINGS", "$scope", "$stateParams", "NgTableParams", "ApiFormatManager", "UserManager", "UserGroupManager", "PasswordManager", "PasswordGroupManager", function(MY_GLOBAL_SETTINGS, $scope, $stateParams, NgTableParams, ApiFormatManager, UserManager, UserGroupManager, PasswordManager, PasswordGroupManager) {

        var self = this;

        PasswordManager.getPassword($stateParams.passwordId).then(function (data) {
            $scope.data = data;
        });

        PasswordGroupManager.getPasswordGroup($stateParams.groupId).then(function (data) {
            $scope.passwordGroup = data;
        }, function(res) {
        });


        $scope.getUserGroups = function(val) {
            var isnum = /^\d+$/.test(val);
            var query = "where id = '" + val.replace(/'/g, "\\'") + "' OR name LIKE '%" + val.replace(/'/g, "\\'") + "%'";

            if (!isnum) {
                query = "where name LIKE '%" + val.replace(/'/g, "\\'") + "%'";
            }

            return UserGroupManager.getUserGroups({
                'query': query
            }).then(function(response){
                return response;
            });
        };

        $scope.userGroup = null;

        $scope.loaded = false;

        $scope.showAlreadyAddedError = false;
        $scope.showOwnUserError = false;

        $scope.currentUser = MY_GLOBAL_SETTINGS.user;

        $scope.typeaheadOnSelect = function($item, $model, $label, $event) {

            PasswordManager.postPasswordUserGroupAccess($stateParams.passwordId, $item.id, 1).then(function (data) {
                self.reloadData();
                $scope.userGroup = null;
            }, function(res) {
                if (res.status === 400 && res.data.errors !== undefined) {
                    $scope.userGroup = null;

                    if (res.data.errors.children.user_group.errors[0] == "User group has already password right.") {
                        $scope.showAlreadyAddedError = true;
                    }
                }
            });

        };

        $scope.selectRights = [
            { id: '', title: 'All'},
            { id: 1, title: 'Read-Only'},
            { id: 2, title: 'Moderator'},
            { id: 3, title: 'Administrator'}
        ];

        $scope.delete = function(passwordAccess) {
            $scope.showOwnUserError = false;

            PasswordManager.deletePasswordUserGroupAccess($stateParams.passwordId, passwordAccess.id).then(function (data) {
                self.reloadData();
            }, function(res) {
                if (res.status === 409) {
                    $scope.showOwnUserError = true;
                    self.reloadData();
                }
            });
        };

        $scope.updateAccess = function(passwordAccess, right) {
            $scope.showOwnUserError = false;

            PasswordManager.putPasswordUserGroupAccess($stateParams.passwordId, passwordAccess.id, right).then(function (data) {
                self.reloadData();
            }, function(res) {
                if (res.status === 409) {
                    $scope.showOwnUserError = true;
                    self.reloadData();
                }
            });
        };

        self.reloadData = function() {
            $scope.tableParams = new NgTableParams({
                page: 1,
                count: 10,
                filter: {'right': ""}
            }, {
                total: 0,
                getData: function (params) {
                    return PasswordManager.getPasswordUserGroupAccesses($stateParams.passwordId, ApiFormatManager.formatNGTableParametersToRest(params)).then(function (data) {
                        $scope.loadingPasswords = false;
                        params.total(data.total);
                        $scope.loaded = true;
                        return data;
                    });
                }
            });
        };

        self.reloadData();
    }]);

    app.controller('ModalEditAccessPasswordGroupController', ["MY_GLOBAL_SETTINGS", "$scope", "$uibModalInstance", "NgTableParams", "ApiFormatManager", "UserManager", "UserGroupManager", "PasswordGroupManager", "passwordGroup", function(MY_GLOBAL_SETTINGS, $scope, $uibModalInstance, NgTableParams, ApiFormatManager, UserManager, UserGroupManager, PasswordGroupManager, passwordGroup) {

        var self = this;

        $scope.passwordGroup = passwordGroup;

        $scope.loading = 2;

        $scope.getUsers = function(val) {
            var isnum = /^\d+$/.test(val);
            var query = "where id = '" + val.replace(/'/g, "\\'") + "' OR full_name LIKE '%" + val.replace(/'/g, "\\'") + "%' OR email LIKE '%" + val.replace(/'/g, "\\'") + "%'";

            if (!isnum) {
                query = "where full_name LIKE '%" + val.replace(/'/g, "\\'") + "%' OR email LIKE '%" + val.replace(/'/g, "\\'") + "%'";
            }

            return UserManager.getUsers({
                'query': query
            }).then(function(response){
                return response;
            });
        };

        $scope.getUserGroups = function(val) {
            var isnum = /^\d+$/.test(val);
            var query = "where id = '" + val.replace(/'/g, "\\'") + "' OR name LIKE '%" + val.replace(/'/g, "\\'") + "%'";

            if (!isnum) {
                query = "where name LIKE '%" + val.replace(/'/g, "\\'") + "%'";
            }

            return UserGroupManager.getUserGroups({
                'query': query
            }).then(function(response){
                return response;
            });
        };

        $scope.user = null;
        $scope.userGroup = null;

        $scope.selectRights = [
            { id: '', title: 'All'},
            { id: 1, title: 'Read-Only'},
            { id: 2, title: 'Moderator'},
            { id: 3, title: 'Administrator'}
        ];


        $scope.showAlreadyAddedError = false;
        $scope.showAlreadyAddedErrorGroup = false;

        $scope.showOwnUserError = false;
        $scope.showOwnUserGroupError = false;

        $scope.currentUser = MY_GLOBAL_SETTINGS.user;

        $scope.typeaheadOnSelect = function($item, $model, $label, $event) {
            $scope.showAlreadyAddedError = false;

            PasswordGroupManager.postPasswordGroupAccess(passwordGroup.id, $item.id, 1).then(function (data) {
                self.reloadData();
                $scope.user = null;
            }, function(res) {

                if (res.status == 400 && res.data.errors !== undefined) {
                    $scope.user = null;
                }

                if (res.status == 400 && res.data.errors !== undefined && res.data.errors.children.user.errors[0] == "User has already password group right.") {
                    $scope.showAlreadyAddedError = true;
                }
            });

        };

        $scope.typeaheadOnSelectGroup = function($item, $model, $label, $event) {
            $scope.showAlreadyAddedErrorGroup = false;

            PasswordGroupManager.postPasswordGroupUserGroupAccess(passwordGroup.id, $item.id, 1).then(function (data) {
                self.reloadGroupData();
                $scope.userGroup = null;
            }, function(res) {

                if (res.status == 400 && res.data.errors !== undefined) {
                    $scope.user = null;
                }

                if (res.status == 400 && res.data.errors !== undefined && res.data.errors.children.user_group.errors[0] == "User group has already password group right.") {
                    $scope.showAlreadyAddedErrorGroup = true;
                }
            });

        };

        $scope.delete = function(passwordGroupAccess) {
            $scope.showOwnUserError = false;
            $scope.showOwnUserGroupError = false;

            PasswordGroupManager.deletePasswordGroupAccess(passwordGroup.id, passwordGroupAccess.id).then(function (data) {
                self.reloadData();
            }, function(res) {
                if (res.status === 409) {
                    $scope.showOwnUserError = true;
                    self.reloadData();
                }
            });
        };

        $scope.deleteGroup = function(passwordGroupAccess) {
            $scope.showOwnUserError = false;
            $scope.showOwnUserGroupError = false;

            PasswordGroupManager.deletePasswordGroupUserGroupAccess(passwordGroup.id, passwordGroupAccess.id).then(function (data) {
                self.reloadGroupData();
            }, function(res) {
                if (res.status === 409) {
                    $scope.showOwnUserGroupError = true;
                    self.reloadGroupData();
                }
            });
        };

        $scope.updateAccess = function(passwordGroupAccess, right) {
            $scope.showOwnUserError = false;
            $scope.showOwnUserGroupError = false;

            PasswordGroupManager.putPasswordGroupAccess(passwordGroup.id, passwordGroupAccess.id, right).then(function (data) {
                self.reloadData();
            }, function(res) {
                if (res.status === 409) {
                    $scope.showOwnUserError = true;
                    self.reloadData();
                }
            });
        };

        $scope.updateUserGroupAccess = function(passwordGroupAccess, right) {
            $scope.showOwnUserError = false;
            $scope.showOwnUserGroupError = false;

            PasswordGroupManager.putPasswordGroupUserGroupAccess(passwordGroup.id, passwordGroupAccess.id, right).then(function (data) {
                self.reloadGroupData();
            }, function(res) {
                if (res.status === 409) {
                    $scope.showOwnUserGroupError = true;
                    self.reloadGroupData();
                }
            });
        };

        self.reloadData = function() {
            $scope.tableParams = new NgTableParams({
                page: 1,
                count: 10,
                filter: {'right': ""}
            }, {
                total: 0,
                getData: function (params) {
                    return PasswordGroupManager.getPasswordGroupAccesses(passwordGroup.id, ApiFormatManager.formatNGTableParametersToRest(params)).then(function (data) {
                        $scope.loading = $scope.loading - 1;
                        params.total(data.total);
                        return data;
                    });
                }
            });
        };

        self.reloadGroupData = function() {
            $scope.tableGroupParams = new NgTableParams({
                page: 1,
                count: 10,
                filter: {'right': ""}
            }, {
                total: 0,
                getData: function (params) {
                    return PasswordGroupManager.getPasswordGroupUserGroupAccesses(passwordGroup.id, ApiFormatManager.formatNGTableParametersToRest(params)).then(function (data) {
                        $scope.loading = $scope.loading - 1;
                        params.total(data.total);
                        return data;
                    });
                }
            });
        };

        self.reloadData();
        self.reloadGroupData();

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    }]);

    app.controller('ModalMovePasswordController', ["MY_GLOBAL_SETTINGS", "$rootScope", "$scope", "$translate", "$uibModalInstance", "ngNotify", "PasswordGroupManager", "PasswordManager", "password", function(MY_GLOBAL_SETTINGS, $rootScope, $scope, $translate, $uibModalInstance, ngNotify, PasswordGroupManager, PasswordManager, password) {

        var self = this;

        $scope.loaded = false;
        $scope.password = password;
        $scope.passwordGroup = null;
        $scope.showSameGroup = false;

        this.defaultGroup = {
            'id': null,
            'name': '------',
            'icon': null,
            'layer': []
        };

        $scope.getGroups = [this.defaultGroup];
        $scope.selected = { state: null };

        this.addGroup = function(layer, group) {
            var layers = [];

            for (var i = 0; i < layer; i++) {
                layers.push(i);
            }

            $scope.getGroups.push({
                'id': group.id,
                'name': group.name,
                'icon': group.icon,
                'right': group.right,
                'layer': layers
            });

            if (group.children !== undefined && group.children.length) {
                angular.forEach(group.children, function (value, key) {
                    self.addGroup(layer+1, value);
                });
            }
        };

        PasswordGroupManager.getPasswordGroups().then(function (data) {
            PasswordManager.getPasswordGroup($scope.password.id).then(function (groupData) {
                $scope.passwordGroup = groupData;

                if ($scope.passwordGroup.parent !== null) {
                    $scope.selected.state = $scope.passwordGroup.parent.id;
                }

                angular.forEach(data, function (value, key) {
                    self.addGroup(0, value);
                });

                $scope.loaded = true;
            });
        });

        $scope.submit = function() {
            if (!$scope.laddaLoading && $scope.selected.state !== null && $scope.selected.state !== '') {
                $scope.laddaLoading = true;
                $scope.showSameGroup = false;

                PasswordManager.putPasswordMove($scope.password.id, $scope.selected.state).then(function() {
                    $scope.laddaLoading = false;

                    ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.TEXT.PASSWORD_MOVED'), 'success');

                    $uibModalInstance.close($scope.selected.state);

                }, function(res) {
                    if (res.status === 409) {
                        $scope.showSameGroup = true;
                    }

                    $scope.laddaLoading = false;
                });
            }
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    }]);

    app.controller('ModalMovePasswordGroupController', ["MY_GLOBAL_SETTINGS", "$rootScope", "$scope", "$translate", "$uibModalInstance", "ngNotify", "PasswordGroupManager", "passwordGroup", function(MY_GLOBAL_SETTINGS, $rootScope, $scope, $translate, $uibModalInstance, ngNotify, PasswordGroupManager, passwordGroup) {

        var self = this;

        $scope.loaded = false;
        $scope.passwordGroup = passwordGroup;

        this.defaultGroup = {
            'id': null,
            'name': '------',
            'icon': null,
            'layer': []
        };

        $scope.getGroups = [this.defaultGroup];
        $scope.selected = { state: null };

        this.addGroup = function(layer, group) {

            if (group.id != $scope.passwordGroup.id) {

                var layers = [];

                for (var i = 0; i < layer; i++) {
                    layers.push(i);
                }

                $scope.getGroups.push({
                    'id': group.id,
                    'name': group.name,
                    'icon': group.icon,
                    'right': group.right,
                    'layer': layers
                });

                if (group.children !== undefined && group.children.length) {
                    angular.forEach(group.children, function (value, key) {
                        self.addGroup(layer+1, value);
                    });
                }

            }
        };

        PasswordGroupManager.getPasswordGroups().then(function (data) {
            angular.forEach(data, function (value, key) {
                self.addGroup(0, value);
            });

            if ($scope.passwordGroup.parent !== null) {
                $scope.selected.state = $scope.passwordGroup.parent.id;
            }

            $scope.loaded = true;
        });

        $scope.submit = function() {

            $scope.laddaLoading = true;

            PasswordGroupManager.putPasswordGroupMove($scope.passwordGroup.id, $scope.selected.state).then(function() {
                $scope.laddaLoading = false;

                $rootScope.$broadcast('reloadPasswordTree');
                ngNotify.set($translate.instant('PASSWORD_GROUP_MANAGEMENT.TEXT.PASSWORD_GROUP_MOVED'), 'success');

                $uibModalInstance.close();

            }, function(res) {
                $scope.laddaLoading = false;
            });
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    }]);

    app.controller('ChangePasswordController', ["MY_GLOBAL_SETTINGS", "$scope", "$translate", "ApiFormatManager", "PasswordManager", function(MY_GLOBAL_SETTINGS, $scope, $translate, ApiFormatManager, PasswordManager) {

        var self = this;
        $scope.passwordChangeAvailable = MY_GLOBAL_SETTINGS.user.type !== 'ldap_user';

        this.data = {
            current_password: '',
            new_password: '',
            new_password_repeat: ''
        };

        this.formErrors = {};

        this.laddaLoading = false;

        this.submit = function() {

            $scope.$broadcast('show-errors-check-validity');

            self.formErrors = {};

            if (self.data.new_password != self.data.new_password_repeat) {
                self.formErrors.new_password_repeat = $translate.instant('VALIDATION.PASSWORD_EQUALS');
            }

            if ($scope.changePasswordForm.$valid && self.data.new_password == self.data.new_password_repeat) {
                self.laddaLoading = true;
                PasswordManager.changePassword(self.data.current_password, self.data.new_password).then(function() {
                    self.laddaLoading = false;
                    self.data = {};
                    $scope.changePasswordForm.$setUntouched();
                    $scope.changePasswordForm.$setPristine();
                    $scope.$broadcast('show-errors-reset');
                    swal($translate.instant('SETTINGS.TEXT.PASSWORD_CHANGED'), $translate.instant('SETTINGS.TEXT.PASSWORD_CHANGED_INFORMATION'), "success");
                }, function(res) {
                    if (res.status == 400 && res.data.errors !== undefined) {
                        $scope.changePasswordForm.$setUntouched();
                        $scope.changePasswordForm.$setPristine();
                        $scope.$broadcast('show-errors-reset');
                        self.formErrors = ApiFormatManager.formatApiFormErrors(res.data.errors.children);
                    }
                    self.laddaLoading = false;
                });
            }
        };
    }]);

    app.controller('PasswordSearchController', ["$scope", "$state", "PasswordManager", function($scope, $state, PasswordManager) {

        var self = this;

        $scope.searchPassword = function(val) {
            return PasswordManager.searchPassword(val.replace(/'/g, "\\'")).then(function(response){
                return response;
            });
        };

        $scope.password = null;

        $scope.typeaheadOnSelect = function($item, $model, $label, $event) {

            $state.go('overview.group.details.main', {groupId: $scope.password.password_group.id, passwordId: $scope.password.id});

            $scope.password = null;
        };
    }]);

    app.controller('PasswordLogController', ["$scope", "$stateParams", "NgTableParams", "ApiFormatManager", "PasswordManager", function($scope, $stateParams, NgTableParams, ApiFormatManager, PasswordManager) {

        var self = this;

        this.loaded = false;

        self.selectLogKeys = [
            { id: '', title: 'All'},
            { id: 'CREATED', title: 'CREATED'},
            { id: 'UPDATED', title: 'UPDATED'},
            { id: 'VIEW', title: 'VIEW'},
            { id: 'SHARED', title: 'SHARED'}
        ];

        self.reloadData = function() {
            $scope.tableParams = new NgTableParams({
                page: 1,
                count: 10,
                filter: {'password_log.key': ""}
            }, {
                total: 0,
                getData: function (params) {
                    return PasswordManager.getPasswordLogs($stateParams.passwordId, ApiFormatManager.formatNGTableParametersToRest(params)).then(function (data) {
                        params.total(data.total);
                        self.loaded = true;
                        return data;
                    });
                }
            });
        };

        self.reloadData();

    }]);

    app.controller('PasswordShareController', ["$location", "$rootScope", "$scope", "$stateParams", "$translate", "NgTableParams", "ngNotify", "ApiFormatManager", "PasswordManager", function($location, $rootScope, $scope, $stateParams, $translate, NgTableParams, ngNotify, ApiFormatManager, PasswordManager) {

        var self = this;

        this.loaded = false;

        $scope.selectKeys = [
            { id: '', title: 'All'},
            { id: '1', title: 'READ'},
            { id: '2', title: 'WRITE'}
        ];

        this.onCopied = function() {
            ngNotify.set($translate.instant('SHARE_LINK.SHARE_LINK_COPIED'), 'success');
        };

        this.onCopyFailed = function(err) {
            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.COPY_FAILED'), 'error');
        };

        $rootScope.$on('shareLinkChange', function(event, mass) {
            self.reloadData();
        });

        this.delete = function(passwordShareLinkId) {
            swal({
                title: $translate.instant('TEXT.ARE_YOU_SURE'),
                text: $translate.instant('SHARE_LINK.DELETE_SHARE_LINK'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $translate.instant('WORDS.YES'),
                cancelButtonText: $translate.instant('WORDS.CLOSE')
            }).then(function() {
                PasswordManager.deletePasswordShare($stateParams.passwordId, passwordShareLinkId).then(function() {

                    ngNotify.set($translate.instant('SHARE_LINK.DELETE_SHARE_LINK_COMPLETE'), 'success');

                    $rootScope.$broadcast('shareLinkChange');

                }, function(res) {
                    if (res.status == 404) {
                        swal($translate.instant('TEXT.RESOURCE_NOT_FOUND'), $translate.instant('TEXT.DELETE_NOT_FOUND'), "error");
                    } else {
                        swal($translate.instant('TEXT.UNKNOWN_ERROR'), $translate.instant('TEXT.UNKNOWN_ERROR_INFORMATION'), "error");
                    }
                });
            }, function(dismiss) {});
        };

        self.reloadData = function() {
            $scope.tableParams = new NgTableParams({
                page: 1,
                count: 10
            }, {
                total: 0,
                getData: function (params) {
                    return PasswordManager.getPasswordShares($stateParams.passwordId, ApiFormatManager.formatNGTableParametersToRest(params)).then(function (data) {

                        angular.forEach(data, function(value, key) {
                            data[key].link = $location.host() + '/password-share#!/password-share/' + value.id + '/' + encodeURI(value.token);
                        });

                        params.total(data.total);
                        self.loaded = true;

                        return data;
                    });
                }
            });
        };

        self.reloadData();

    }]);

})();
