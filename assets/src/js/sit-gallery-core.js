var $ = jQuery.noConflict();

(function ($) {

    'use strict';

    function SITg_app() {

        var self = this;

        self.$ajaxRemoveImages = false;
        self.$ajaxGetImages = false;

        self.confirmDelete = false;

        self.$window = $(window);
        self.$document = $(document);
        self.$body = $("body");

        self.$modalUploader = $("#sitg-uploader");
        self.$dragDropZone = $("#drag-and-drop-zone");
        self.$filesList = $("#files");
        self.inputFileList = [];

        self.$btnDelete = $(".sitg-btn-delete");
        self.$btnDeleteAll = $(".sitg-btn-delete-all");
        self.$selectSort = $("#sitg-sort");

        self.$imagePreviewList = $("#sitg-image-preview-list");
        self.sitgSortable = "";
        self.selectedItemsCache = [];

        self.init();

    }

    SITg_app.prototype = {

        init: function () {

            var self = this;

            // Event handlers
            self.onBind();

            self.bodyScrollClass(self);

        },

        onBind: function(){

            var self = this;

            self.$window.on('scroll', function () {
                self.bodyScrollClass(self);
            });
/*
            self.$window.on("beforeunload", function(e) {
                // Kdyz se zmeni poradi budem to hlasit
                // Musi se to ulozit, pokud chceme
                if( self.$body.hasClass("sitg-sort-changed") ){
                    return "Změnili jste řazení. Když jej neuložíte, nebude se zobrazovat.";
                }
                //else if(){
                    // Kdyz se obrazky promazou, ukladaji se data do post_meta
                    // Tohle by se melo dit asi automaticky, aby na to uzivatel nemusel myslet
                //}
                else {
                    delete e['returnValue'];
                }

            });
*/
            // Button, ktery otevira modalni okno s uploadem
            self.$body.on("click", ".sitg-modal-open", function (e) {
                self.$body.addClass("modal-open");
                self.$modalUploader.show();
                // Init uploader
                self.initUploader();
                e.preventDefault();
            });

            // Rucne spustene nahravani fotek
            self.$body.on("click", "#sitg-upload-start", function(e){
                self.$dragDropZone.dmUploader('start');
                e.preventDefault();
            });

            // Rucne zastavime nahravani
            self.$body.on("click", "#sitg-upload-cancel", function(e){
                self.$dragDropZone.dmUploader('cancel');
                e.preventDefault();
            });

            // Smazani obrazku v modalu
            self.$body.on("click", ".sitg-modal-delete-file", function(e){

                var $this = $(this);

                var id = $this.data("id");

                // Nejdriv zjistime, jestli uploadujeme nebo ne
                // Kdyz spoustime upload - pridavame ty dragdrop zone tridu is-running
                if( self.$dragDropZone.hasClass("is-running") ){
                    // Stop upload pro vybrany soubor
                    // Bohuzel se uz muze neco nahrat nebo se to ulozi do DB
                    // Meli bysme zaridit, aby se pripadny fragmenty smazaly taky
                    self.$dragDropZone.dmUploader("cancel", id);
                }
                // Kdyz mame ve fronte soubor, ktery neprosel validaci
                // ma ten handler tridu delete-row-only
                // Smazeme jen to HTML-ko
                // Ve fronte to nemame
                else if( $this.hasClass("delete-row-only") ){
                    $this.closest("li").slideUp(200, function(){
                        $this.remove();
                    });
                }
                else {
                    // Smazeme soubor z fronty pred uploadovanim
                    // Metodu "delete" jsme do toho pluginu pridali - neaktulizovat!
                    // Kdyz upload bezi, tahle metoda nedela nic
                    // Soubor se z fronty odstrani, ale HTML tam zustane
                    // To se musi zaridit tady
                    self.$dragDropZone.dmUploader('delete', id);
                    // Odstranime polozku ze seznamu v tom modalu
                    $this.closest("li").slideUp(200, function(){
                        $this.remove();
                    });
                }

                e.preventDefault();
            });

            // Button, ktery zavira modalni okno s uploadem
            self.$body.on("click", ".sitg-modal-close", function (e) {
                self.modalClose();
                e.preventDefault();
            });

            // Razeni obrazků
            self.$selectSort.on("change", function(){
                var vle = this.value;
                self.ajaxGetImages( $(this), vle );
                self.$body.addClass("sitg-sort-changed");
            });

            // Zobrazit editaci obrazku
            self.$body.on("click", ".sitg-handler-edit", function(e){
                // Hide all
                self.editImageClose();
                // Show current
                $(this).closest(".sitg-mb-list-ui-handle").find(".sitg-mb-edit-layer").addClass("is-open").fadeIn(200);

                e.preventDefault();
            });

            // Uloz zmeny editace nazvu obrazku
            self.$body.on("click", ".sitg-handler-edit-save", function(e){
                self.ajaxEditImageHandler( $(this) );

                e.preventDefault();
            });

            // Uloz zmeny, kdyz se klepne na Enter v tom textovym poli
            self.$body.on("keypress", ".sitg-mb-edit-title", function(e){
                var keycode = (e.keyCode ? e.keyCode : e.which);
                if( keycode == '13' ){
                    self.ajaxEditImageHandler( $(this) );
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            // Zavrit editaci obrazku
            self.$body.on("click", ".sitg-handler-edit-close", function(e){
                // Pokud se ten novy nazev neulozi, vlozime do toho input pole ten stary
                self.editImageCacheTitle( $(this) );
                e.preventDefault();
            });

            // Zavri editaci obrazku pres klavesu ESC
            self.$document.keydown(function (e) {
                if (e.cancelable === true && e.key === 'Esc' || e.key === 'Escape' || e.keyCode === 27) {
                    // Pokud se ten novy nazev neulozi, vlozime do toho input pole ten stary
                    self.editImageCacheTitle( $(".sitg-mb-edit-layer.is-open") );
                }
            });

            // Zavri editaci obrazku kdyz klepnes mimo
            self.$body.on("mousedown", function(e) {
                var container = $(".sitg-mb-edit-layer.is-open");
                if (!container.is(e.target) && container.has(e.target).length === 0){
                    // Pokud se ten novy nazev neulozi, vlozime do toho input pole ten stary
                    self.editImageCacheTitle( container );
                }
            });

            // Oznaceni "hlavniho" obrazku
            self.$body.on("click", ".sitg-handler-featured", function(){
                self.ajaxSetFeaturedImage( $(this) );
            });

            // Mazani obrazku - vsech oznacenych
            self.$btnDelete.on("click", function(e){
                // Vytahneme si vsechny oznacene obrazky
                if( self.selectedItemsCache.length > 0 ) {
                    // Chceme to nejdriv potvrdit
                    self.confirmDialogShow("Máme je opravdu smazat?", "sitgDeleteSelected");
                }

                e.preventDefault();
            });

            // Smazat vse
            self.$btnDeleteAll.on("click", function(e){
                // Chceme to nejdriv potvrdit
                self.confirmDialogShow("Smažeme je všechny?", "sitgDeleteAll");
                e.preventDefault();
            });

            self.$body.on("click", "#sitg-confirm-yes", function(e){

                var action = $(this).closest("#sitg-confirm-dialog").data("action");
                self.$document.trigger( action );

                e.preventDefault();
            });

            self.$body.on("click", "#sitg-confirm-no", function(e){
                self.confirmDialogHide();
                e.preventDefault();
            });

            // Vlastni eventy
            // ---------------------------------------------------
            // sitgDeleteSelected pro mazani jen vybranych polozek
            self.$document.on("sitgDeleteSelected", function(){

                var $items  = $(self.selectedItemsCache);
                // Schovame dialog
                self.confirmDialogHide();
                // Pak je poslem pres ajax ke zpracovani
                self.ajaxRemoveImagesHandler($items);
                // Nakonec vysypeme cache s vybranyma polozkama
                self.selectedItemsCache = [];

            });
            // sitgDeleteAll pro mazani vsech polozek
            self.$document.on("sitgDeleteAll", function(){
                var $items  = self.$imagePreviewList.find(".sitg-mb-list-ui-handle");
                self.confirmDialogHide();
                self.ajaxRemoveImagesHandler( $items );
            });

            // Tohle se spousti jenom tehdy, pokud mame v metaboxu nejaky obrazky
            if( self.$imagePreviewList.find(".sitg-mb-list-ui-handle").length ){

                // Sortovani a vybirani
                // https://github.com/SortableJS/Sortable
                self.initSortable();
            }

        },

        initSortable: function(){

            var self = this;

            // Nejdriv zabit potom ozivit
            // Kdyz se nam zmeni DOM je to nutny
            if( typeof self.sitgSortable !== "undefined" && self.sitgSortable !== "" ) {
                self.sitgSortable.destroy();
            }

            // https://github.com/SortableJS/Sortable
            // https://github.com/SortableJS/Sortable/tree/master/plugins/MultiDrag
            self.sitgSortable = new Sortable( $("#sitg-image-preview-list")[0], {
                multiDrag: true,
                selectedClass: "ui-selected",
                // Pripneme to na element uvnitr .sitg-mb-list-ui-handle
                // Protoze jinak se to da chytit i za ty okraje, coz je divny chovani
                handle: ".sitg-mb-list-item",
                filter: ".sitg-mb-buttons", // Tlacitko uvnitr
                animation: 200,
                // Kdyz se vybira a kdyz se sortuje, tak si ty vybrany polozky nahazime do promenny jako cache
                // aby jsme o ne neprisli
                // Kdyz se totiz klepne na tlacitko s nejakou akci, odznaci se to a nemuzeme tak nic posilat
                // Niz je pak listener, ktery posloucha, jestli se kleplo na tlacitko nebo ne
                // Tu cache pak vysypeme, kdyz se klepne mimo
                // Vysypeme ji taky, kdyz se na tlacitko klepne, ale az potom, co se to posle PHP-cku
                onSelect: function(evt){
                    self.selectedItemsCache = evt.items;
                },
                onUpdate: function (evt){
                    self.selectedItemsCache = evt.items;
                    //self.ajaxUpdateSort();
                }
            });

            // Kdyz se klepne mimo tlacitko delete tak se vymaze cache s oznacenyma obrazkama
            self.$body.on("mousedown", function(e) {
                var container = $(".sitg-btn-delete, #sitg-confirm-yes");
                if (!container.is(e.target) && container.has(e.target).length === 0){
                    self.selectedItemsCache = [];
                }
            });

        },

        initUploader: function(){

            var self = this;

            self.$dragDropZone.dmUploader({

                url: sitg_ajax.ajax_url,
                extraData: {
                    _ajax_nonce: sitg_ajax.nonce,
                    action: 'sitg_ajax_upload',
                    post_id: self.$dragDropZone.data("id")
                },
                maxFileSize: sitg_ajax.max_upload_size,
                allowedTypes: 'image/*',
                extFilter: ['jpg', 'jpeg','png','gif'],
                auto: false,
                queue: true,

                onDragEnter: function(){

                    // Happens when dragging something over the DnD area
                    this.addClass('active');

                },
                onDragLeave: function(){

                    // Happens when dragging something OUT of the DnD area
                    this.removeClass('active');

                },
                onInit: function(){

                    // Plugin is ready to use
                    self.ui_add_log('Penguin initialized :)', 'info');

                },
                onComplete: function(){

                    // Kdyz je hotovo, odstranime tridu is-running, podle ni detekujeme, jestli to bezi nebo ne
                    self.uploaderToggleClass();

                    // All files in the queue are processed (success or error)
                    self.ui_add_log('All pending tranfers finished');

                },
                onNewFile: function(id, file, validate){

                    self.ui_multi_add_file(id, file, validate);

                    if (typeof FileReader !== "undefined"){

                        var reader = new FileReader();
                        var img = $('#uploaderFile' + id).find('img');
                        var $del = $('#uploaderFile' + id).find(".sitg-modal-delete-file");

                        reader.onload = function (e) {
                            img.attr( 'src', e.target.result );
                            $del.data("id", id);
                        };

                        reader.readAsDataURL( file );

                    }

                    // When a new file is added using the file selector or the DnD area
                    self.ui_add_log('Nová fotka: #' + id);

                },
                onFileTypeError: function(file){

                    self.ui_add_log('File \'' + file.name + '\' cannot be added: file type is not allowed', 'danger');

                },
                onBeforeUpload: function(id){

                    // Pridame dragdrop zone tridu is-running pro detekci, jestli to bezi nebo ne
                    self.uploaderToggleClass("run");

                    self.ui_multi_update_file_status(id, 'uploading', 'Nahrávání...');
                    self.ui_multi_update_file_progress(id, 0, '', true);

                    // about tho start uploading a file
                    self.ui_add_log('Starting the upload of #' + id);

                },
                onUploadCanceled: function(id) {

                    if( !id ){
                        // Kdyz se stopne upload uplne - musime tu tridu odstranit
                        self.uploaderToggleClass();
                    }

                    // Kdyz se to stopne, tak PHP-cko dal jede
                    // Budem muset smazat co se nahralo
                    console.log("canceled");

                    // Happens when a file is directly canceled by the user.
                    self.ui_multi_update_file_status(id, 'warning', 'Nahrávání zrušeno');
                    self.ui_multi_update_file_progress(id, 0, 'warning', false);

                },
                onUploadProgress: function(id, percent){

                    // Updating file progress
                    self.ui_multi_update_file_progress(id, percent);

                },
                onUploadSuccess: function(id, data){

                    var data_str = JSON.stringify( data ),
                        data_obj = JSON.parse( data_str );

                    self.ajaxGetImage( data_obj.file_id );

                    // Restartujeme ten sortable
                    self.initSortable();

                    self.ui_multi_update_file_status(id, 'success', 'Úspěšně jsme to nahráli :)');
                    self.ui_multi_update_file_progress(id, 100, 'success', false);

                    // A file was successfully uploaded
                    self.ui_add_log('Server Response for file #' + id + ': ' + JSON.stringify(data));
                    self.ui_add_log('Upload of file #' + id + ' COMPLETED', 'success');

                },
                onUploadError: function(id, xhr, status, message){

                    self.ui_multi_update_file_status(id, 'danger', message);
                    self.ui_multi_update_file_progress(id, 0, 'danger', false);

                },
                onFallbackMode: function(){

                    // When the browser doesn't support this plugin :(
                    self.ui_add_log('Plugin cant be used here, running Fallback callback', 'danger');

                },
                onFileSizeError: function( file ){

                    self.ui_add_log('File \'' + file.name + '\' cannot be added: size excess limit', 'danger');
                }

            });

        },

        ui_multi_add_file: function( id, file, validate ) {

            var self = this;

            // Prenasime info o validaci
            // V tom seznamu to zobrazime
            // Musime ale zajistit, aby se ten soubor neobjevil ve fronte

            // Do ty JS sablony vlozime jen obrazek a nazev a stav (proc nejde nahrat)
            // Zbytek pujde do riti

            self.$filesList.find('li.empty').fadeOut(); // remove the 'no files yet'

            var template;

            if( validate !== true ){
                template = $("#no-validate-files-template").text();
                template = template.replace( "%%validate_message%%", validate.message );
            }
            else {
                template = $("#files-template").text();
            }

            template = template.replace( "%%filename%%", file.name );

            template = $(template);
            template.prop('id', 'uploaderFile' + id);
            template.data('file-id', id);

            self.$filesList.prepend(template);

        },

        // Updates a file progress, depending on the parameters it may animate it or change the color.
        ui_multi_update_file_progress: function( id, percent, color, active ) {

            color = (typeof color === 'undefined' ? false : color);
            active = (typeof active === 'undefined' ? true : active);

            var bar = $('#uploaderFile' + id).find('div.progress-bar');

            bar.width(percent + '%').attr('aria-valuenow', percent);
            bar.toggleClass('progress-bar-striped progress-bar-animated', active);

            if (percent === 0){
                bar.html('');
            } else {
                bar.html(percent + '%');
            }

            if (color !== false){
                bar.removeClass('bg-success bg-info bg-warning bg-danger');
                bar.addClass('bg-' + color);
            }
        },

        // Changes the status messages on our list
        ui_multi_update_file_status: function( id, status, message ) {

            $('#uploaderFile' + id).find('.sitg-status').html(message).prop('class', 'sitg-status status text-' + status);

        },

        // Adds an entry to our debug area
        ui_add_log: function(message, color) {

            var d = new Date();

            var dateString = (("0" + d.getHours())).slice(-2) + ":" +
                (("0" + d.getMinutes())).slice(-2) + ":" +
                (("0" + d.getSeconds())).slice(-2);

            color = (typeof color === "undefined" ? "muted" : color);

            var template = $("#debug-template").text();
            template = template.replace("%%date%%", dateString);
            template = template.replace("%%message%%", message);
            template = template.replace("%%color%%", color);

            $("#debug").find("li.empty").fadeOut(); // remove the 'no messages yet'
            $("#debug").prepend(template);

        },

        ajaxGetImage: function( id ){

            var self = this;

            $.ajax({

                url: sitg_ajax.ajax_url,
                cache: false,
                dataType: 'html',
                data: {
                    _ajax_nonce: sitg_ajax.nonce,
                    action: 'sitg_ajax_get_image',
                    pid: id
                }

            })
            .done(function( data ){

                // Odstranime hlasku, ze nejsou vybrany zadny obrazky
                $("#no-images-meta-info").remove();
                // Vlozime obrazek
                self.$imagePreviewList.append( data );

            })
            .fail(function( jqXHR, textStatus ){
                console.log( 'SITg: AJAX error - ajaxGetImage() - ' + textStatus );
            });

        },

        ajaxGetImages: function( $this, value ){

            var self = this;

            if( self.$ajaxGetImages )
                return false;

            self.$ajaxGetImages = $.ajax({
                url: sitg_ajax.ajax_url,
                method: "POST",
                cache: false,
                dataType: 'html',
                data: {
                    _ajax_nonce: sitg_ajax.nonce,
                    action: 'sitg_ajax_get_images',
                    post_id: $this.data("id"),
                    sort: value
                }
            })
            .done(function( data ){
                self.$imagePreviewList.html( data );
                self.$ajaxGetImages = false;
            })
            .fail(function( jqXHR, textStatus ){
                console.log( 'SITg: AJAX error - ajaxRemoveImages() - ' + textStatus );
                self.$ajaxGetImages = false;
            });

        },

        ajaxRemoveImagesHandler: function( $items ){

            var self = this;

            // Kdyby se podarilo klepnout na to mazani
            // kdyz bezi ajax - musime to stopnout
            if( self.$ajaxRemoveImages )
                return false;

            // Prazdny data nebudem resit
            if( $items.length < 1 )
                return false;

            var img_ids = [];

            // Sestavime seznam idcek
            $items.each( function(i, e) {
                img_ids[i] = $(this).data("pid");
            });

            var i       = 0,
                max     = img_ids.length;

            function nextRemoveImg() {

                if (i >= max) {

                    // Az nam to dobehne, povolime dalsi akce
                    self.$ajaxRemoveImages = false;

                    // Aktualizujeme poradi v DB a na strance
                    // Je tam animace toho mizeni obrazku
                    // Musime tam dat kratsi timeout, nez se ten DIV odstrani uplne
                    // To sortovani volame jednou, tak by to nemelo vadit
                    //var t = setTimeout(function(){
                    //    self.ajaxUpdateSort();
                    //    clearTimeout(t);
                    //}, 900 );

                    return true;
                }

                $.ajax({
                    url: sitg_ajax.ajax_url,
                    method: "POST",
                    cache: false,
                    dataType: 'json',
                    data: {
                        _ajax_nonce: sitg_ajax.nonce,
                        action: 'sitg_ajax_remove_one_image',
                        pid: img_ids[i]
                    }
                })
                .done(function( data ){

                    onRemoveImgComplete( data );

                })
                .fail(function( jqXHR, textStatus ){
                    console.log( 'SITg: AJAX error - ajaxRemoveImages() - ' + textStatus );
                });

            }

            function onRemoveImgComplete( data ) {

                //console.log( "onRemoveImgComplete: " + img_ids[i] );

                // Kdyz se v PHP-cku nic nepokazi, vraci to 1
                // jinak 0
                // Nemelo by se to v tom seznamu nejak pekne zobrazit?
                // Ze to jako nejde smazat?
                if( data === 1 ){

                    // Odstranime nahled obrazku ze seznamu
                    var $item = $('[data-pid='+ img_ids[i] +']');
                    // Nejdriv animujeme pruhlednost
                    $item.find(".sitg-mb-list-item").fadeTo(150, 0, function(){
                        // Jakmile to dojede animujeme sirku a vysku na nula
                        $item.animate({ height: 0 }, 150, function(){
                            $item.animate({ width: 0 }, 150, function(){
                                // Nakonec smazeme element aby nam tam nedelal bordel
                                $item.remove();
                            });
                        });
                    });

                }

                i++;
                nextRemoveImg();
            }

            // Prvni iteraci pustime rucne
            nextRemoveImg();

        },

        ajaxEditImageHandler: function( $this ){

            var self = this;

            var $parent = $this.closest(".sitg-mb-list-ui-handle");
            var $layer = $parent.find(".sitg-mb-edit-layer");
            var title = $layer.find(".sitg-mb-edit-title").val();
            var pid = $parent.data("pid");

            $.ajax({
                url: sitg_ajax.ajax_url,
                method: "POST",
                cache: false,
                dataType: 'json',
                data: {
                    _ajax_nonce: sitg_ajax.nonce,
                    action: 'sitg_ajax_edit_image',
                    pid: pid,
                    title: title
                }
            })
            .done(function( data ){

                var $el = $('.sitg-mb-list-ui-handle[data-pid="'+ pid + '" ]');

                if( data.response > 0 ){
                    $el.find(".sitg-mb-list-item-title strong").text( title );
                    $el.find(".sitg-mb-edit-title").data("cache", title );
                    self.editImageClose();
                }

            })
            .fail(function( jqXHR, textStatus ){
                console.log( 'SITg: AJAX error - ajaxEditImageHandler() - ' + textStatus );
            });

        },

        ajaxSetFeaturedImage: function( $this ){

            var $this_closest_handle = $this.closest(".sitg-mb-list-ui-handle"),
                selected = 0;

            // Pokud klepneme na uz vybrany obrazek, odznacime ho
            // php-cku posleme selected = 1
            if( $this_closest_handle.hasClass("is-featured") ){
                selected = 1;
            }

            // Reset selected item
            $(".sitg-mb-list-ui-handle").removeClass("is-featured");

            // Get IDs
            var post_id = $("#sitg-image-preview-list").data("post-id");
            var pid = $this_closest_handle.data("pid");

            // Call ajax
            $.ajax({
                url: sitg_ajax.ajax_url,
                method: "POST",
                cache: false,
                data: {
                    _ajax_nonce: sitg_ajax.nonce,
                    action: 'sitg_ajax_set_featured_image',
                    pid: pid,
                    post_id: post_id,
                    selected: selected
                }
            })
            .done(function( data ){
                console.log(data);
                // Set current
                if( data !== "unselected" ){
                    $this_closest_handle.addClass("is-featured");
                }
            })
            .fail(function( jqXHR, textStatus ){
                console.log( 'SITg: AJAX error - ajaxSetFeaturedImage() - ' + textStatus );
            });

        },

        editImageCacheTitle: function( $this ){

            var $parent, $input;

            $(".sitg-mb-edit-layer").fadeOut(200, function(){
                $parent = $this.closest(".sitg-mb-list-ui-handle");
                $input = $parent.find(".sitg-mb-edit-title");
                $input.val( $input.data("cache") );
                $(this).removeClass("is-open");
            });

        },

        editImageClose: function(){
            $(".sitg-mb-edit-layer").fadeOut(200).removeClass("is-open");
        },

        modalClose: function(){

            var self = this;

            // Zavreme okno a zabijeme ten uploader
            self.$modalUploader.toggle();
            self.$body.toggleClass("modal-open");
            self.$dragDropZone.dmUploader("destroy");

            // Vyprazdnime ten seznam nahranych fotech
            self.$filesList.find(".media").remove();
            self.$filesList.find(".empty").show();

            // Seznam nahranych souboru hodime do metaboxu jako input hidden pole
            // Cele to vygenerujeme z DB
            // Co ale s obrazkama, ktery uz tam jsou?

        },

        confirmDialogShow: function( title, action ){

            var self = this;

            self.$body.addClass("modal-open");

            var template = $("#sitg-confirm-dialog-template").text();
                template = template.replace( "%%title%%", title );

            var $template = $(template);

                $template.addClass("show");
                $template.attr('data-action', action);

                self.$body.prepend( $template );

        },

        confirmDialogHide: function(){
            var self = this;
            var $dialog = $("#sitg-confirm-dialog");
            $dialog.remove();
            self.$body.removeClass("modal-open");
        },

        uploaderToggleClass: function( state ){

            var self = this;

            if( state === "run" ){
                $("#sitg-upload-cancel").css({display: "inline-block"});
                $("#sitg-modal-close-btn").css({display:"none"});
                self.$dragDropZone.addClass("is-running");
            }
            else {
                $("#sitg-upload-cancel").css({display: "none"});
                $("#sitg-modal-close-btn").css({display:"inline-block"});
                self.$dragDropZone.removeClass("is-running");
            }

        },

        bodyScrollClass: function(self){

            var $el = $("#sit_custom_gallery");

            if( $el.length > 0 ) {
                if (self.$document.scrollTop() > $("#sit_custom_gallery").offset().top) {
                    self.$body.addClass('sitg-stick-on');
                } else {
                    self.$body.removeClass('sitg-stick-on');
                }
            }

        },
        getHostname: function(url) {
            var m = url.match(/^http:\/\/[^/]+/);
            return m ? m[0] : null;
        }

    };

    // Add core script to $.sitgApp so it can be extended
    //$.sitgApp = SITg_app.prototype;

    $(document).ready(function () {

        // Initialize script
        new SITg_app();

    });

})(jQuery);
