<tr id="transaction-<?php echo $transaction->getId(); ?>"<?php echo ($alternate) ? ' class="alternate"' : ''; ?>>
	<?php
		if ($transaction->getUser()) {
			$user = get_user_by('id', $transaction->getUser());
			$user = $user->last_name . ' ' . $user->first_name;
		} else {
			$user = '';
		}
		$data = $transaction->getData();
	?>
    <td><?php echo $transaction->getId(); ?></td>
    <td><?php echo $user; ?></td>
    <td><?php echo $transaction->getDate(); ?></td>
    <td><?php echo $transaction->getAmount(); ?></td>
    <td><?php echo $data['mena']; ?></td>
    <td><?php echo $data['uzivatel']; ?></td>
    <td><?php echo $data['komentar']; ?></td>
    <td><?php echo $data['sprava']; ?></td>
</tr>
