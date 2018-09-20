(function ($) {
    acf.fields.simplewysiwyg = acf.field.extend({
        type: 'simple_wysiwyg',
        $el: null,
        $textarea: null,
        toolbars: {},
        actions: {
            'load': 'initialize',
            'append': 'initialize',
            'remove': 'disable',
            'sortstart': 'disable',
            'sortstop': 'enable'
        },
        focus: function () {
            // get elements
            this.$el = this.$field.find('.wp-editor-wrap').last();
            this.$textarea = this.$el.find('textarea');

            // get options
            this.o = acf.get_data(this.$el);
            this.o.id = this.$textarea.attr('id');
        },
        initialize: function () {
            // bail early if no tinyMCEPreInit (needed by both tinymce and quicktags)
            if (typeof tinyMCEPreInit === 'undefined') return;

            // generate new id
            var old_id = this.o.id,
                new_id = acf.get_uniqid('acf-editor-'),
                html = this.$el.outerHTML();

            // replace
            html = acf.str_replace(old_id, new_id, html);

            // swap
            this.$el.replaceWith(html);

            // update id
            this.o.id = new_id

            // initialize
            this.initialize_tinymce();
            this.initialize_quicktags();
        },
        initialize_tinymce: function () {

            // bail early if no tinymce
            if (typeof tinymce === 'undefined') return;

            // vars
            var mceInit = this.get_mceInit();

            // append
            tinyMCEPreInit.mceInit[mceInit.id] = mceInit;

            // bail early if not visual active
            if (!this.$el.hasClass('tmce-active')) return;

            // initialize
            try {

                tinymce.init(mceInit);

            } catch (e) {
            }

        },

        initialize_quicktags: function () {

            // bail early if no quicktags
            if (typeof quicktags === 'undefined') return;

            // vars
            var qtInit = this.get_qtInit();

            // append
            tinyMCEPreInit.qtInit[qtInit.id] = qtInit;

            // initialize
            try {

                var qtag = quicktags(qtInit);

                this._buttonsInit(qtag);

            } catch (e) {
            }

        },

        get_mceInit: function () {

            // reference
            var $field = this.$field;

            // vars
            var toolbar = this.get_toolbar(this.o.toolbar),
                mceInit = $.extend({}, tinyMCEPreInit.mceInit.acf_content);

            // selector
            mceInit.selector = '#' + this.o.id;

            // id
            mceInit.id = this.o.id; // tinymce v4
            mceInit.elements = this.o.id; // tinymce v3

            // toolbar
            if (toolbar) {

                var k = (tinymce.majorVersion < 4) ? 'theme_advanced_buttons' : 'toolbar';

                for (var i = 1; i < 5; i++) {

                    mceInit[k + i] = acf.isset(toolbar, i) ? toolbar[i] : '';

                }

            }

            // events
            if (tinymce.majorVersion < 4) {

                mceInit.setup = function (ed) {

                    ed.onInit.add(function (ed, event) {

                        // focus
                        $(ed.getBody()).on('focus', function () {

                            acf.validation.remove_error($field);

                        });

                        $(ed.getBody()).on('blur', function () {

                            // update the hidden textarea
                            // - This fixes a bug when adding a taxonomy term as the form is not posted and the hidden textarea is never populated!

                            // save to textarea
                            ed.save();

                            // trigger change on textarea
                            $field.find('textarea').trigger('change');

                        });

                    });

                };

            } else {

                mceInit.setup = function (ed) {

                    ed.on('focus', function (e) {

                        acf.validation.remove_error($field);

                    });

                    ed.on('change', function (e) {

                        // save to textarea
                        ed.save();


                        $field.find('textarea').trigger('change');

                    });

                };

            }

            // disable wp_autoresize_on (no solution yet for fixed toolbar)
            mceInit.wp_autoresize_on = false;

            // hook for 3rd party customization
            mceInit = acf.apply_filters('wysiwyg_tinymce_settings', mceInit, mceInit.id);

            // return
            return mceInit;

        },

        get_qtInit: function () {

            // vars
            var qtInit = $.extend({}, tinyMCEPreInit.qtInit.acf_content);


            // id
            qtInit.id = this.o.id;

            // hook for 3rd party customization
            qtInit = acf.apply_filters('wysiwyg_quicktags_settings', qtInit, qtInit.id);

            // return
            return qtInit;

        },

        /*
         *  disable
         *
         *  This function will disable the tinymce for a given field
         *  Note: txtarea_el is different from $textarea.val() and is the value that you see, not the value that you save.
         *        this allows text like <--more--> to wok instead of showing as an image when the tinymce is removed
         *
         *  @type	function
         *  @date	1/08/2014
         *  @since	5.0.0
         *
         *  @param	n/a
         *  @return	n/a
         */

        disable: function () {

            try {

                // vars
                var ed = tinyMCE.get(this.o.id)


                // save
                ed.save();


                // destroy editor
                ed.destroy();

            } catch (e) {
            }

        },

        enable: function( id ){

            this.enable_tinymce( id );

        },

        enable_tinymce: function( id ){

            // bail early
            if( typeof switchEditors === 'undefined' ) return false;


            // bail early if not initialized
            if( typeof tinyMCEPreInit.mceInit[ id ] === 'undefined' ) return false;


            // toggle
            switchEditors.go( id, 'tmce');


            // return
            return true;

        },

        get_toolbar: function (name) {

            // bail early if toolbar doesn't exist
            if (typeof this.toolbars[name] !== 'undefined') {

                return this.toolbars[name];

            }


            // return
            return false;

        },


        /*
         *  _buttonsInit
         *
         *  This function will add the quicktags HTML to a WYSIWYG field. Normaly, this is added via quicktags on document ready,
         *  however, there is no support for 'append'. Source: wp-includes/js/quicktags.js:245
         *
         *  @type	function
         *  @date	1/08/2014
         *  @since	5.0.0
         *
         *  @param	ed (object) quicktag object
         *  @return	n/a
         */

        _buttonsInit: function (ed) {
            var defaults = ',strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,';

            canvas = ed.canvas;
            name = ed.name;
            settings = ed.settings;
            html = '';
            theButtons = {};
            use = '';

            // set buttons
            if (settings.buttons) {
                use = ',' + settings.buttons + ',';
            }

            for (i in edButtons) {
                if (!edButtons[i]) {
                    continue;
                }

                id = edButtons[i].id;
                if (use && defaults.indexOf(',' + id + ',') !== -1 && use.indexOf(',' + id + ',') === -1) {
                    continue;
                }

                if (!edButtons[i].instance || edButtons[i].instance === inst) {
                    theButtons[id] = edButtons[i];

                    if (edButtons[i].html) {
                        html += edButtons[i].html(name + '_');
                    }
                }
            }

            if (use && use.indexOf(',fullscreen,') !== -1) {
                theButtons.fullscreen = new qt.FullscreenButton();
                html += theButtons.fullscreen.html(name + '_');
            }


            if ('rtl' === document.getElementsByTagName('html')[0].dir) {
                theButtons.textdirection = new qt.TextDirectionButton();
                html += theButtons.textdirection.html(name + '_');
            }

            ed.toolbar.innerHTML = html;
            ed.theButtons = theButtons;

        },

    });


    var acf_content = acf.model.extend({

        $div: null,

        actions: {
            'ready': 'ready'
        },

        ready: function () {

            // vars
            this.$div = $('#acf-hidden-wp-editor');

            // bail early if doesn't exist
            if (!this.$div.exists()) return;

            // move to footer
            this.$div.appendTo('body');

            // bail early if no tinymce
            if (typeof tinymce === 'undefined') return;

            // restore default activeEditor
            tinymce.on('AddEditor', function (data) {

                // vars
                var editor = data.editor;

                // update WP var to match tinymce
                wpActiveEditor = editor.id;

                // bail early if not acf_content
                if (editor.id !== 'acf_content') return;

                // update global vars
                tinymce.activeEditor = tinymce.editors.content || null;
                wpActiveEditor = tinymce.editors.content ? 'content' : null;
            });
        }
    });
})(jQuery);