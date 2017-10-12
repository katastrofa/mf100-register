<tr class="user-<?php echo $year; ?>-<?php echo $user->ID; ?>"<?php echo ($alternate) ? ' class="alternate"' : ''; ?>>
    <td class="id"><?php echo $user->ID; ?></td>
    <td class="name">
        <strong><a href="#" class="edit"><?php echo $user->last_name . ' ' . $user->first_name; ?></a></strong>
        <div class="row-actions">
            <span class="edit"><a href="#" class="edit">Edit</a> | </span>
            <?php if ($user->isRegistered($year)) : ?>
                <span class="delete"><a href="#" class="delete">Unregister</a></span>
            <?php else : ?>
                <span class="delete"><a href="#" class="delete">Register</a></span>
            <?php endif; ?>
        </div>
    </td>
    <td data-field="field-user_email" class="editable"><?php echo $user->user_email; ?></td>

    <?php $paymentKey = Mf100User::REG_KEY . '_' . $year . '_pay'; ?>
    <td data-field="field-<?php echo $paymentKey; ?>" class="editable"><?php echo $user->$paymentKey; ?></td>

    <?php $userOptions = Mf100UserOptions::getInstance(); ?>
    <?php foreach ($fields as $value) : ?>
        <?php $display = ($userOptions->isFieldVisible($value)) ? '' : 'style="display: none;"'; ?>
        <td data-field="field-<?php echo $value; ?>" class="field-<?php echo $value; ?> editable" <?php echo $display; ?>><?php echo $user->$value; ?></td>
    <?php endforeach; ?>
    <td class="resend-reg"><input type="button" class="button-small resend-reg resend-<?php echo $year; ?>-<?php echo $user->ID; ?>" value="Resend email" /></td>
    <td class="save-edit" style="display: none;">
        <input type="button" class="button-small save" value="Save" />
        <input type="button" class="button-small cancel" value="Cancel" />
    </td>
</tr>
