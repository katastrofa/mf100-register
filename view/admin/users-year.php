<div class="mf100-reg-table-wrap year-<?php echo $year; ?>"<?php echo (!$first) ? ' style="display: none;"' : ''; ?>>
    <?php if ($reg) : ?>
        <h2>Registered</h2>
    <?php else : ?>
        <h2>Unregistered</h2>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed users">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col" class="manage-column column-name sortable desc">Meno</th>
                <th scope="col" class="manage-column column-email sortable desc">Email</th>
                <th scope="col">Platba</th>
                <?php $userOptions = Mf100UserOptions::getInstance(); ?>
                <?php foreach ($fields as $value) : ?>
                    <?php $display = ($userOptions->isFieldVisible($value)) ? '' : 'style="display: none;"'; ?>
                    <th scope="col" class="mf100-<?php echo $value; ?>" <?php echo $display; ?>><?php echo $value; ?></th>
                <?php endforeach; ?>
                <th scope="col">&nbsp;</th>
                <th scope="col" class="save-edit" style="display: none;">&nbsp;</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th scope="col">ID</th>
                <th scope="col" class="manage-column column-name sortable desc">Meno</th>
                <th scope="col" class="manage-column column-email sortable desc">Email</th>
                <th scope="col">Platba</th>
                <?php foreach ($fields as $value) : ?>
                    <?php $display = ($userOptions->isFieldVisible($value)) ? '' : 'style="display: none;"'; ?>
                    <th scope="col" class="mf100-<?php echo $value; ?>" <?php echo $display; ?>><?php echo $value; ?></th>
                <?php endforeach; ?>
                <th scope="col">&nbsp;</th>
                <th scope="col" class="save-edit" style="display: none;">&nbsp;</th>
            </tr>
        </tfoot>

        <tbody>
            <?php $alternate = false; ?>
            <?php foreach ($users as &$user) : ?>
                <?php $alternate = $alternate xor true; ?>
                <?php $this->showTemplate('user-line', array('user' => $user, 'alternate' => $alternate, 'fields' => $fields, 'year' => $year)); ?>
            <?php endforeach; ?>
        </tbody>

    </table>
</div>