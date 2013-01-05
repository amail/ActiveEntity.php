<?php

	include("../lib/ActiveEntity/Loader.php");

	include("Configuration.php");
	include("Entities/Todo.php");
	include("Entities/TodoCollection.php");

	$todos = new TodoCollection($data_store);

	if(isset($_POST['action'])){
		switch($_POST['action']){
			case 'add':
				if(isset($_POST['text']) && strlen($_POST['text']) > 0){
					$todo = new Todo($data_store, array(), $todos);
					$todo->setText($_POST['text']);
					$todo->setCreated(time());
					$todo->save();
				}
				break;
			case 'toggle_completed':
				if(isset($_POST['todo_id'])){
					$todo = new Todo($data_store, array("id" => $_POST['todo_id']), $todos);
					$todo->setCompleted(!$todo->getCompleted());
					$todo->save();
				}
				break;
			case 'remove':
				if(isset($_POST['todo_id'])){
					$todo = new Todo($data_store, array("id" => $_POST['todo_id']), $todos);
					$todo->remove();
				}
				break;
		}
	}

?>

<!DOCTYPE html>
<html lang="en-us">
    <head>
        <meta charset="utf-8">
        <title>Todo Example - ActiveEntity</title>
		<style>
			table {
				text-align: left;
				width: 700px;
			}
			h3 {
				background-color: #f0f0f0;
				padding: 5px;
			}
		</style>
    </head>
    <body>
		<h1><a href="Todo.php">Todo - ActiveEntity</a></h1>
		<div>
			<h3>Todos</h3>
			<table>
				<thead>
					<th>Text</th>
					<th>Created</th>
					<th>Completed?</th>
				</thead>
				<tbody>
					<?php if($todos->count() == 0){ ?>
						<tr>
							<td rowspan="4"><i>Nothing to do yet...</i></td>
						</tr>
					<?php }else{ ?>
						<?php foreach($todos as $todo){ ?>
							<tr>
								<td><?php echo($todo->getText()) ?></td>
								<td><?php echo(date("Y-m-d H:i:s", $todo->getCreated())) ?></td>
								<td>
									<form method="post">
										<input type="hidden" name="action" value="toggle_completed" />
										<input type="hidden" name="todo_id" value="<?php echo($todo->getId()) ?>" />
										<input type="checkbox" onclick="this.form.submit();" <?php echo($todo->getCompleted() ? 'CHECKED' : '') ?>/>
									</form>
								</td>
								<td>
									<form method="post">
										<input type="hidden" name="action" value="remove" />
										<input type="hidden" name="todo_id" value="<?php echo($todo->getId()) ?>" />
										<input type="submit" value="Remove" />
									</form>
								</td>
							</tr>
						<?php } ?>
					<?php } ?>
				</tbody>
			</table>

			<br />

			<form method="post">
				<input type="hidden" name="action" value="add" />
				<input name="text" type="text" size="75" placeholder="Enter something to do!" />
				<input type="submit" value="Add" />
			</form>
		</div>

		<h3>Redis Logs</h3>
		<div>
			<table>
				<thead>
					<th>Created</th>
					<th>Message</th>
				</thead>
				<tbody>
					<?php foreach($data_store->getLogger()->getLogs() as $log){ ?>
						<tr>
							<td><?php echo(date("H:i:s", $log["created"])) ?></td>
							<td><?php echo($log["message"]) ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</html>