<tr id="user-<?php echo $transaction->getId(); ?>"<?php echo ($alternate) ? ' class="alternate"' : ''; ?>>
    <td><?php echo $transaction->getId(); ?></td>
    <td><?php echo $transaction->getUser(); ?></td>
    <td><?php echo $transaction->getDate(); ?></td>
    <td><?php echo $transaction->getAmount(); ?></td>
    <td><?php echo $transaction->getData()['mena']; ?></td>
    <td><?php echo $transaction->getData()['uzivatel']; ?></td>
    <td><?php echo $transaction->getData()['komentar']; ?></td>
</tr>
