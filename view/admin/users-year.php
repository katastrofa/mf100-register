<div class="mf100-reg-table-wrap" id="year-<?php echo $year; ?>"<?php echo (!$first) ? ' style="display: none;"' : ''; ?>>
    <table class="wp-list-table widefat fixed users">
        <thead>
            <tr>
                <th scope="col" id="name" class="manage-column column-name sortable desc">Meno</th>
                <th scope="col" id="email" class="manage-column column-email sortable desc">Email</th>
                <?php $userOptions = Mf100UserOptions::getInstance(); ?>
                <?php foreach ($fields as $value) : ?>
                    <?php $display = ($userOptions->isFieldVisible($value)) ? '' : 'style="display: none;"'; ?>
                    <th scope="col" id="mf100-col-<?php echo $value; ?>" class="mf100-<?php echo $value; ?>" <?php echo $display; ?>><?php echo $value; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th scope="col" class="manage-column column-name sortable desc">Meno</th>
                <th scope="col" class="manage-column column-email sortable desc">Email</th>
                <?php foreach ($fields as $value) : ?>
                    <?php $display = ($userOptions->isFieldVisible($value)) ? '' : 'style="display: none;"'; ?>
                    <th scope="col" class="mf100-<?php echo $value; ?>" <?php echo $display; ?>><?php echo $value; ?></th>
                <?php endforeach; ?>
            </tr>
        </tfoot>

        <tbody id="the-list">
            <?php $alternate = false; ?>
            <?php foreach ($users as &$user) : ?>
                <?php $alternate = $alternate xor true; ?>
                <?php $user->meta = $this->prepareMeta(get_user_meta($user->ID)); ?>
                <?php $this->showTemplate('user-line', array('user' => $user, 'alternate' => $alternate, 'fields' => $fields)); ?>
            <?php endforeach; ?>
        </tbody>

    </table>
</div>