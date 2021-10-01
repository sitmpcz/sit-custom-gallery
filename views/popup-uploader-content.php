<div class="sitg-modal-row">
    <div class="sitg-modal-col">

        <!-- Our markup, the important part here! -->
        <div id="drag-and-drop-zone" class="dm-uploader p-5" data-id="<?php echo $post->ID; ?>" data-debug="<?php echo $data_debug_attr; ?>">
            <h3 class="mb-5 mt-5 text-muted">Chytni &amp; přetáhni fotky sem</h3>

            <div class="btn button btn-block mb-5">
                <span>Vyberte fotky z počítače</span>
                <input type="file" id="sitg-input-file" title="Click to add Files" multiple>
            </div>
        </div><!-- /uploader -->

        <p class="sitg-btn-row">
            <button id="sitg-upload-cancel" class="sitg-upload-cancel button button-danger button-large">Zastavit <i class="dashicons dashicons-dismiss"></i></button>
            <button id="sitg-upload-start" class="sitg-upload-start button button-primary button-large">Spustit nahrávání fotek <i class="dashicons dashicons-controls-play"></i></button>
        </p>

    </div>
    <div class="sitg-modal-col">
        <div class="sitg-modal-filelist">
            <h3 class="sitg-modal-filelist-title">Seznam fotek k nahrávání</h3>
            <ul class="list-unstyled p-2 d-flex flex-column col" id="files">
                <li class="text-muted text-center empty">Žádné fotky nejsou vybrány.</li>
            </ul>
        </div>
    </div>
</div><!-- /file list -->

<!-- Confirm dialog -->
<script type="text/html" id="sitg-confirm-dialog-template">
    <div id="sitg-confirm-dialog">
        <div class="sitg-confirm-modal" role="dialog" tabindex="0">
            <div class="sitg-confirm-content">
                <h3 class="sitg-confirm-title">%%title%%</h3>
                <div class="sitg-confirm-btn-group">
                    <button id="sitg-confirm-no" class="sitg-confirm-btn button">Ne</button>
                    <button id="sitg-confirm-yes" class="sitg-confirm-btn button button-primary">Ano</button>
                </div>
            </div>
        </div>
        <div class="media-modal-backdrop"></div>
    </div>
</script>
<!-- /Confirm dialog -->

<!-- File item template -->
<script type="text/html" id="files-template">
    <li class="media">
        <div class="mr-3 mb-2 preview-img">
            <img src="<?php echo plugin_dir_url( __DIR__ ).'assets/dist/img/placeholder-238x238.png'; ?>" alt="Náhled">
        </div>
        <div class="media-body mb-1">
            <p class="sitg-status-bar mb-2">
                <strong>%%filename%%</strong><br>
                <span class="sitg-status-content">Stav: <span class="sitg-status text-muted">Čeká se, než se spustí nahrávání :)</span></span>
            </p>
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                     role="progressbar"
                     style="width: 0;"
                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            <hr class="mt-1 mb-1">
        </div>
        <div class="sitg-modal-delete-file">
            <span class="dashicons dashicons-no-alt"></span>
        </div>
    </li>
</script>

<!-- File item template -->
<script type="text/html" id="no-validate-files-template">
    <li class="media">
        <div class="mr-3 mb-2 preview-img">
            <img src="<?php echo plugin_dir_url( __DIR__ ).'assets/dist/img/placeholder-238x238.png'; ?>" alt="Náhled">
        </div>
        <div class="media-body mb-1">
            <p class="sitg-status-bar mb-2">
                <strong>%%filename%%</strong><br>
                <span class="sitg-status-content">Chyba: <span class="sitg-status text-danger">%%validate_message%%</span></span>
            </p>
            <div class="progress"></div>
            <hr class="mt-1 mb-1">
        </div>
        <div class="sitg-modal-delete-file delete-row-only">
            <span class="dashicons dashicons-no-alt"></span>
        </div>
    </li>
</script>

<?php if( SITG_DEBUG === true ): ?>
    <div class="card h-100">
        <div class="card-header">
            Debug Messages
        </div>

        <ul class="list-group list-group-flush" id="debug">
            <li class="list-group-item text-muted empty">Loading plugin....</li>
        </ul>
    </div><!-- /debug -->
    <!-- Debug item template -->
    <script type="text/html" id="debug-template">
        <li class="list-group-item text-%%color%%"><strong>%%date%%</strong>: %%message%%</li>
    </script>
<?php endif; ?>
