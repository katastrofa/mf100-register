<tr id="user-<?php echo $user->ID; ?>"<?php echo ($alternate) ? ' class="alternate"' : ''; ?>>
    <td><?php echo $user->last_name . ' ' . $user->first_name; ?></td>
    <td><?php echo $user->data->user_email; ?></td>
    <td><?php echo $user->meta['mobil']; ?></td>
    <td><?php echo $user->meta['obec']; ?></td>
    <td><?php echo $user->meta['klub']; ?></td>
</tr>
