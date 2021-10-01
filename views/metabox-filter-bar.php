<div class="sitg-filter-bar">
    <div class="sitg-field-group">
        <label for="sitg-sort">Řazení:</label>
        <select name="sitg_sort" id="sitg-sort" data-id="<?php echo $post->ID; ?>">
            <option value="custom_asc">Řazení - nahoru</option>
            <option value="custom_desc">Řazení - dolů</option>
            <option value="date_asc">Podle datumu - nejstarší první</option>
            <option value="date_desc">Podle datumu - nejmladší první</option>
            <option value="name_asc">Podle názvu - A/Z</option>
            <option value="name_desc">Podle názvu - Z/A</option>
        </select>
    </div>
    <div class="sitg-btn-group sitg-field-group-right">
        <button class="sitg-btn-delete sitg-button" title="Smazat vybrané položky"><span class="dashicons dashicons-trash"></span></button>
        <button class="sitg-modal-open sitg-button" title="Přidat fotky"><span class="dashicons dashicons-plus"></span></button>
    </div>
</div>
