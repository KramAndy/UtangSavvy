<?php
	include 'auth.php';
	include 'config.php';
	include 'helper.php';
	
	$q = isset($_GET['q']) ? trim($_GET['q']) : "";
	$like = "%" . $q . "%";
	
	$sql = "SELECT * FROM debtors";
	
	if ($q !== ""){
		$sql .= " WHERE name LIKE ?
				OR tier LIKE ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $like, $like);
		$stmt->execute();
		$result = $stmt->get_result();
	}else{
		$result = $conn->query("SELECT * FROM debtors");
	}
while($row = $result->fetch_assoc()):
?>
<tr>
					  <td><?= htmlspecialchars($row['name']); ?></td>
					  <td><?= htmlspecialchars($row['contact']); ?></td>
					  <td>
						<?= htmlspecialchars($row['tier']); ?>
					  </td>
					  <td>
						<a href="view_ledger.php?debtor_id=<?= $row['id']; ?>">View Ledger</a>
					  </td>
					  <td>
					  <form method="POST" action="edit_tier.php" style="margin-top:5px;">
						  <input type="hidden" name="debtor_id" value="<?= $row['id']; ?>">
						  <select name="tier" required>
							<option value="Bronze" <?= $row['tier'] == 'Bronze' ? 'selected' : '' ?>>Bronze</option>
							<option value="Silver" <?= $row['tier'] == 'Silver' ? 'selected' : '' ?>>Silver</option>
							<option value="Gold" <?= $row['tier'] == 'Gold' ? 'selected' : '' ?>>Gold</option>
							<option value="Platinum" <?= $row['tier'] == 'Platinum' ? 'selected' : '' ?>>Platinum</option>
						  </select>
						  <button type="submit" class="button" style="margin-left:5px;">Update</button>
						</form>
					  </td>
					</tr>
					<?php endwhile; ?>