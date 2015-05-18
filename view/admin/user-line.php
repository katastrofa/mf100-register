<tr id="user-<?php echo $user->ID; ?>"<?php echo ($alternate) ? ' class="alternate"' : ''; ?>>
    <td>
        <strong><a href="#" class="edit"><?php echo $user->last_name . ' ' . $user->first_name; ?></a></strong>
        <div class="row-actions">
            <span class="edit"><a href="#" class="edit">Edit</a> | </span>
            <span class="delete"><a href="#" class="delete">Unregister</a></span>
        </div>
    </td>
    <td><?php echo $user->data->user_email; ?></td>
    <?php $userOptions = Mf100UserOptions::getInstance(); ?>
    <?php foreach ($fields as $value) : ?>
        <?php $display = ($userOptions->isFieldVisible($value)) ? '' : 'style="display: none;"'; ?>
        <td class="mf100-<?php echo $value; ?>" <?php echo $display; ?>><?php echo $user->meta[$value]; ?></td>
    <?php endforeach; ?>
</tr>
