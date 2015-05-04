<div class="mf100-reg-table-wrap" id="year-<?php echo $year; ?>"<?php echo (!$first) ? ' style="display: none;"' : ''; ?>>
    <table class="wp-list-table widefat fixed users">
        <thead>
            <tr>
                <th scope="col" id="name" class="manage-column column-name sortable desc">Meno</th>
                <th scope="col" id="email" class="manage-column column-email sortable desc">Email</th>
                <th scope="col" id="phone" class="manage-column">Mobil</th>
                <th scope="col" id="obec" class="manage-column">Obec</th>
                <th scope="col" id="klub" class="manage-column">Klub</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th scope="col" class="manage-column column-name sortable desc">Meno</th>
                <th scope="col" class="manage-column column-email sortable desc">Email</th>
                <th scope="col" class="manage-column">Mobil</th>
                <th scope="col" class="manage-column">Obec</th>
                <th scope="col" class="manage-column">Klub</th>
            </tr>
        </tfoot>

        <tbody id="the-list">
            <?php $alternate = false; ?>
            <?php foreach ($users as &$user) : ?>
                <?php $alternate = $alternate xor true; ?>
                <?php $user->meta = $this->prepareMeta(get_user_meta($user->ID)); ?>
                <?php $this->showTemplate('user-line', array('user' => $user, 'alternate' => $alternate)); ?>
            <?php endforeach; ?>
        </tbody>

    </table>
</div>