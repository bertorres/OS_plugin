<!DOCTYPE html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="css/styles.termo.css" rel="stylesheet" type="text/css">
		<link href="css/styles.css" rel="stylesheet" type="text/css">
	</head>
	<?php 
		include ('../../../inc/includes.php');
		include ('../../../config/config.php');
		global $DB;
		Session::checkLoginUser();
		Html::header('OS', "", "plugins", "os");
		echo Html::css($CFG_GLPI["root_doc"]."/css/styles.css");
		if (isset($_SESSION["glpipalette"])) {
			echo Html::css($CFG_GLPI["root_doc"]."/css/palettes/".$_SESSION["glpipalette"].".css");
		}
		if (isset($_GET['id'])) {
				$OsId = $_GET['id'];
			}
		else{
			$OsId = "";
		}
		//recoger los datos del formulario de configuración
		$SelPlugin = "SELECT * FROM glpi_plugin_os_config";
		$ResPlugin = $DB->query($SelPlugin);
		$Plugin = $DB->fetch_assoc($ResPlugin);
		$EmpresaPlugin = $Plugin['name'];
		$EnderecoPlugin = $Plugin['address'];
		$TelefonePlugin = $Plugin['phone'];
		$CidadePlugin = $Plugin['city'];
		$CorPlugin = $Plugin['color'];
		$CorTextoPlugin = $Plugin['textcolor'];
		$TarefasPlugin = $Plugin["tarefas"];
		$CustosPlugin = $Plugin["custos"];
		$Moeda = $Plugin["moeda"];
		//seleccionar ticket y atributos
		$SelTicket = "SELECT * FROM glpi_tickets WHERE id = '".$OsId."'";
		$ResTicket = $DB->query($SelTicket);
		$Ticket = $DB->fetch_assoc($ResTicket);
		$OsNome = $Ticket['name'];
		$OsDescricao = $Ticket['content'];
		$OsSolucao = $Ticket['solution'];
		$EntidadeId = $Ticket['entities_id'];
		//fecha incial
		$SelDataInicial = "SELECT date,date_format(date, '%d/%m/%Y %H:%i') AS DataInicio FROM glpi_tickets WHERE id = '".$OsId."'";
		$ResDataInicial = $DB->query($SelDataInicial);
		$DataInicial = $DB->fetch_assoc($ResDataInicial);
		$OsData = $DataInicial['DataInicio'];
		//fecha final
		$SelDataFinal = "SELECT due_date,date_format(solvedate, '%d/%m/%Y %H:%i') AS DataFim FROM glpi_tickets WHERE id = '".$OsId."'";
		$ResDataFinal = $DB->query($SelDataFinal);
		$DataFinal = $DB->fetch_assoc($ResDataFinal);
		$OsDataEntrega = $DataFinal['DataFim'];
		//usuarios asociados al ticket
		$SelTicketUsers = "SELECT * FROM glpi_tickets_users WHERE tickets_id = '".$OsId."'";
		$ResTicketUsers = $DB->query($SelTicketUsers);
		$TicketUsers = $DB->fetch_assoc($ResTicketUsers);
		$OsUserId = $TicketUsers['users_id'];
		//usuario asginado
		$SelIdOsResponsavel = "SELECT users_id FROM glpi_tickets_users WHERE tickets_id = '".$OsId."' AND type = 2";
		$ResIdOsResponsavel = $DB->query($SelIdOsResponsavel);
		$IdOsResponsavel = $DB->fetch_assoc($ResIdOsResponsavel);
		$SelOsResponsavelName = "SELECT * FROM glpi_users WHERE id = '".$IdOsResponsavel['users_id']."'";
		$ResOsResponsavelName = $DB->query($SelOsResponsavelName);
		$OsResponsavelFull = $DB->fetch_assoc($ResOsResponsavelName);
		$OsResponsavel = $OsResponsavelFull['firstname']. " " .$OsResponsavelFull['realname'];
		//entidad y sus datos
		$SelEmpresa = "SELECT * FROM glpi_entities WHERE id = '".$EntidadeId."'";
		$ResEmpresa = $DB->query($SelEmpresa);
		$Empresa = $DB->fetch_assoc($ResEmpresa);
		$EntidadeName = $Empresa['name'];
		$EntidadeCep = $Empresa['postcode'];
		$EntidadeEndereco = $Empresa['address'];
		$EntidadeEmail = $Empresa['email'];
		$EntidadePhone = $Empresa['phonenumber'];
		$EntidadeCnpj = $Empresa['comment'];
		//email usuario
		$SelEmail = "SELECT * FROM glpi_useremails WHERE users_id = '".$OsUserId."'";
		$ResEmail = $DB->query($SelEmail);
		$Email = $DB->fetch_assoc($ResEmail);
		$UserEmail = $Email['email'];
		//custos 
		$SelCustoLista = "SELECT actiontime, sec_to_time(actiontime) AS Hora,name,cost_time,cost_fixed,cost_material,FORMAT(cost_time,2,'de_DE') AS cost_time2, FORMAT(cost_fixed,2,'de_DE') AS cost_fixed2, FORMAT(cost_material,2,'de_DE') AS cost_material2, SUM(cost_material + cost_fixed + cost_time * actiontime/3600) AS CustoItem FROM glpi_ticketcosts WHERE tickets_id = '".$OsId."' GROUP BY id";
		$ResCustoLista = $DB->query($SelCustoLista);
		$SelCusto = "SELECT SUM(cost_material + cost_fixed + cost_time * actiontime/3600) AS SomaTudo FROM glpi_ticketcosts WHERE tickets_id = '".$OsId."'";
		$ResCusto = $DB->query($SelCusto);
		$Custo = $DB->fetch_assoc($ResCusto);
		$CustoTotal =  $Custo['SomaTudo'];
		$CustoTotalFinal = number_format($CustoTotal, 2, ',', ' ');
		$SelTempoTotal = "SELECT SUM(actiontime) AS TempoTotal FROM glpi_ticketcosts WHERE tickets_id = '".$OsId."'";
		$ResTempoTotal = $DB->query($SelTempoTotal);
		$TempoTotal = $DB->fetch_assoc($ResTempoTotal);
		$seconds = $TempoTotal['TempoTotal'];
		$hours = floor($seconds / 3600);
		$seconds -= $hours * 3600;
		$minutes = floor($seconds / 60);
		$seconds -= $minutes * 60;
		//tarefas
		$SelTarefas = "SELECT distinct tasks.content as 'TITULO', DATE_FORMAT(tasks.date, '%a %d %b %Y %H:%i') as 'FECHA CREACION', DATE_FORMAT(tasks.end, '%a %d %b %Y %H:%i') as 'FECHA FIN',
				tasks.actiontime as 'DURACION', tasks.state as 'ESTADO', users.name as 'USUARIO'
				FROM glpi_tickettasks as tasks 
				LEFT JOIN glpi_users as users 
				ON users.id = tasks.users_id_tech 
				WHERE tickets_id = '".$OsId."'";
		$ResTarefas = $DB->query($SelTarefas);
		$SelNumTarefas = "SELECT COUNT(*) FROM glpi_tickettasks WHERE tickets_id = '".$OsId."'";
		$ResulNumTarefas = $DB->query($SelNumTarefas);
		$NumTarefasArray = $DB->fetch_row($ResulNumTarefas);
		$NumTarefas = $NumTarefasArray[0];
	?>
	<body>
		<!-- inicio dos botoes -->
		<div class="botoes"> 
			<!--<input type="button" class="botao" name="configurar" value="Configurar" onclick="window.location.href='./index.php'"> -->
			<p></p>
			<form action="os.php" method="get">	
				<input type="text" name="id" value="" placeholder="Digite o ID" />
				<input class="submit" type="submit" value="Enviar">
			</form>
			<p></p>
			<a href="#" class="vsubmit" onclick="window.print();"> Imprimir </a>
			<a href='os_cli.php?id=<?php echo $OsId; ?>' class="vsubmit"> Cliente </a>
			<a href='os.php?id=<?php echo $OsId; ?>' class="vsubmit"> Empresa </a>
			<a href="index.php" class="vsubmit" style="float:right;"> Configurar </a>
			<p></p>
		</div>
		<!-- inicio da tabela -->
		<table id="principal" border="1"> 
			<tr>
				<!-- tabela do logotipo -->
				<td class="logotipo">
					<img src="./img/logo_os.png" width="119" height="58" align="absmiddle"> 
				</td>	
				<!-- titulo -->
				<td class="titulo">
					<p><font size="4"><?php echo ($EmpresaPlugin);?></font></p>
					<p><font size="2"><?php echo ("$EnderecoPlugin - $CidadePlugin - $TelefonePlugin"); ?></font></p>
				<!-- titulo segunda linha -->
					<p class="p_titulo"> OS Nº &nbsp;<?php echo $OsId;?></p> 
				</td>
			</tr>
			<tr>
				<th class="encabezado" colspan="2" style="background-color:<?php echo $CorPlugin; ?> !important";>
					<font color="<?php echo $CorTextoPlugin; ?>">DADOS DO CLIENTE</font>
				</th> 
			</tr>
			<tr>
				<td width="50%"><b>Empresa: </b><?php echo ($EntidadeName)?></td>
				<td ><b>Telefone: </b><?php echo ($EntidadePhone)?></td> 
			</tr>
			<tr>
				<td width="50%"><b>Endereço: </b><?php echo ($EntidadeEndereco)?></td>
				<td><b>E-Mail: </b><?php echo ($EntidadeEmail)?></td> 
			</tr>
			<tr>
				<td width="50%"><b>CNPJ: </b><?php echo ($EntidadeCnpj)?></td>
				<td ><b>CEP: </b><?php echo ($EntidadeCep)?></td> 
			</tr>
			<!-- tabela OS -->
			<tr>
				<th class="encabezado" colspan="2" style="background-color:<?php echo $CorPlugin; ?> !important";>
					<font color="<?php echo $CorTextoPlugin; ?>">DETALHES DA ORDEM DE SERVIÇO</font>
				</th> 
			</tr>
			<tr>
				<td width="50%">
					<b>Título:</b> <?php echo $OsNome;?> 
				</td>
				<td width="50%">
					<b>Responsável:</b> <?php echo $OsResponsavel;?> 
				</td> 
			</tr>
			<tr>
				<td width="50%">
					<b>Data/Hora de Início: </b><?php echo ($OsData);?> 
				</td>
				<td>
					<b>Data/Hora de Término: </b><?php echo ($OsDataEntrega);?> 
				</td>
			</tr>
			<tr style="background-color:<?php echo $CorPlugin; ?> !important";>
				<font color="<?php echo $CorTextoPlugin; ?>">
					<th class="encabezado">DESCRIÇÃO</th>
					<th class="encabezado">SOLUÇÃO</th>
				</font>
			</tr>
			<tr>
				<td class="texto">
					<?php echo html_entity_decode($OsDescricao);?> 
				</td> 
				<td class="texto">
		<?php 
			if ( $OsSolucao == null ) {
				echo "<br><hr><br><hr><br><hr><br>";
			} else {
				echo html_entity_decode($OsSolucao);
			}
		?>
		</td></tr>
		<?php
			if ( $NumTarefas > 0 && $TarefasPlugin == 1 ){
				$i = 0;
				echo "<tr><th class='encabezado' colspan=2 style=background-color:$CorPlugin><font color=$CorTextoPlugin >TAREFAS REALIZADAS</font></th></tr>";
				echo '<tr><td colspan="2">';
				echo "<table class='custos'>";
				echo '<tr>';
				echo '<th>TITLE</th>';
				echo '<th>DATA DE CRIAÇÃO</th>';
				echo '<th>DURAÇÃO</th>';
				echo '<th>USUARIO</th>';
				echo '<th>ESTADO</th>';
				echo '<th>DATA FINAL</th>';
				echo '</tr>';
				while($Fila = $DB->fetch_assoc($ResTarefas)){
					$i++;
					$SecondsTarefas = $Fila["DURACION"];
					$hours_tarefas = floor($SecondsTarefas/3600);
					$SecondsTarefas -= $hours_tarefas * 3600;
					$minuts_tarefas = floor($SecondsTarefas/60);
					if ($Fila['ESTADO'] == 0) {
						$estado = "informação";
					}
					elseif ($Fila['ESTADO'] == 1) {
						$estado = "por fazer";
					}
					else{
					 	$estado = "feito";
					}
					if($Fila['FECHA FIN'] == NULL){
						$fecha_fin = '<hr>';
					}
					else{
						$fecha_fin = $Fila['FECHA FIN'];
					}
						echo '<tr>';
						echo '<td>'.$Fila['TITULO'].'</td>';
						echo '<td>'.$Fila['FECHA CREACION'].'</td>';
						echo '<td>'.$hours_tarefas.'h '.$minuts_tarefas.'min</td>';
						echo '<td>'.$Fila['USUARIO'].'</td>';
						echo '<td>'.$estado.'</td>';
						echo '<td>'.$fecha_fin.'</td>';
						echo '</tr>';
				}
				echo '</table></tr>';
		}
		if ( $CustoTotalFinal != 0 && $CustosPlugin == 1 ){
			echo "<tr><th class='encabezado' colspan=2 style=background-color:$CorPlugin><font color=$CorTextoPlugin >DETALHES DE CUSTO</font></th></tr>";
			echo '<tr><td colspan="2">';
			echo "<table class='custos'>";
			echo '<tr>';
			echo '<th>DESCRIÇÃO</th>';
			echo '<th>CUSTO FIXO</th>';
			echo '<th>CUSTO DE MATERIAL</th>';
			echo '<th>CUSTO POR HORA</th>';
			echo '<th>DURAÇÃO</th>';
			echo '<th>CUSTO</th>';
			echo '</tr>';
			while($Escrita = $DB->fetch_assoc($ResCustoLista)){
					echo '<tr>';
					echo '<td>'.$Escrita['name'].'</td>';
					echo '<td>'.$Escrita['cost_fixed2'].' '.$Moeda.'</td>';
					echo '<td>'.$Escrita['cost_material2'].' '.$Moeda.'</td>';
					echo '<td>'.$Escrita['cost_time2'].' '.$Moeda.'</td>';
					echo '<td>'.$Escrita['Hora'].'</td>';
					echo '<td>'; 
					echo number_format($Escrita['CustoItem'], 2, ',', '.');
					echo ' '.$Moeda.'</td>'; 
					echo '</tr>';
			}
			echo '<tr><td colspan="3"><p class="totales"><b>DURAÇÃO TOTAL:</b> '.$hours.'h '.$minutes.'min '.$seconds.'seg</p></td></tr>';
			echo '<tr>';
			echo '<tr><td colspan="3"><p class="totales"><b>CUSTO TOTAL:</b> '.$CustoTotalFinal.' '.$Moeda.'</td></p></tr>';
			echo '</table></td></tr>';
		}
	?>
			<tr>
				<th class="encabezado" colspan="2" style="background-color:<?php echo $CorPlugin; ?> !important";><font color="<?php echo $CorTextoPlugin; ?>">ASSINATURAS</font>
				</th> 
			</tr>
			<tr align="center">
				<td rowspan="4" style="text-align:center;"><br><br>____________________________________<br>
				<?php echo ($EntidadeName);?></td>
				<td rowspan="2" style="text-align:center;"><br><br>_____________________________________<br>
				<?php echo ($EmpresaPlugin);?></td> 
		</table> 
		<!-- estilo do botao para nao aparecer em impressao --> 
		<style type="text/css" media="print">
			@media print {
				div.botoes {
					display:none;
				}
			}
		</style>
	</body>
</html>