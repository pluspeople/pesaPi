<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PesaPi configuration tool</title>
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

		<script type="text/javascript">
		function updateAccounts() {
  		var package = {AJAX: 'getAccountList'};
			$.post('', package, function(data){
					$('#accountsList').html(data);
			});
		}

    function createDb() {
			var package = {AJAX: 'createDb'};

			$.post('', package, function(data){
					if (data == "OK") {
		        document.location.reload();
					}
			});
		}

    function addPrivateAccount(context) {
			var package = {
					AJAX: 'addPrivateAccount',
					type: context.find('input[name="type"]').val(),
          name: context.find('input[name="name"]').val(),
          identifier: context.find('input[name="identifier"]').val(),
          pushIn: context.find('input:checkbox[name|="pushIn"]').is(':checked') ? "1" : "0",
          pushInUrl: context.find('input[name="pushInUrl"]').val(),
          pushInSecret: context.find('input[name="pushInSecret"]').val(),
          pushOut: context.find('input:checkbox[name|="pushOut"]').is(':checked') ? "1" : "0",
          pushOutUrl: context.find('input[name="pushOutUrl"]').val(),
          pushOutSecret: context.find('input[name="pushOutSecret"]').val(),
          pushNeutral: context.find('input:checkbox[name|="pushNeutral"]').is(':checked') ? "1" : "0",
          pushNeutralUrl: context.find('input[name="pushNeutralUrl"]').val(),
          pushNeutralSecret: context.find('input[name="pushNeutralSecret"]').val()
			};

			$.post('', package, function(data){
					if (data == "OK") {
							context.slideUp();
							resetForm(context);
							updateAccounts();
					}
			});
		}

    function addMpesaPaybill(context) {
			var package = {
					AJAX: 'addMpesaPaybill',
					type: context.find('input[name="type"]').val(),
          name: context.find('input[name="name"]').val(),
          identifier: context.find('input[name="identifier"]').val(),
          pushIn: context.find('input:checkbox[name|="pushIn"]').is(':checked') ? "1" : "0",
          pushInUrl: context.find('input[name="pushInUrl"]').val(),
          pushInSecret: context.find('input[name="pushInSecret"]').val(),
          pushOut: context.find('input:checkbox[name|="pushOut"]').is(':checked') ? "1" : "0",
          pushOutUrl: context.find('input[name="pushOutUrl"]').val(),
          pushOutSecret: context.find('input[name="pushOutSecret"]').val(),
          pushNeutral: context.find('input:checkbox[name|="pushNeutral"]').is(':checked') ? "1" : "0",
          pushNeutralUrl: context.find('input[name="pushNeutralUrl"]').val(),
          pushNeutralSecret: context.find('input[name="pushNeutralSecret"]').val(),
		      certificate: context.find('input[name="certificate"]').val(),
		      organisation: context.find('input[name="organisation"]').val(),
		      login: context.find('input[name="login"]').val(),
		      password: context.find('input[name="password"]').val()
			};

			$.post('', package, function(data){
					if (data == "OK") {
							context.slideUp();
							resetForm(context);
							updateAccounts();
					}
			});
		}

		function testPaybillCertificate(iden) {
			var package = {
					AJAX: 'TestCertificate',
          identifier: iden
			};

			$.post('', package, function(data) { 
		    if (data == "OK") {
		      alert('The certificate works perfect');
				} else {
		      alert('The certificate did not work!!! Possible issues: The certificate is in the wrong format, The certificate does not contain the PRIVATE KEYS, The certificate is expired. Please verify that the certificate works using CURL on a command line before trying again');
		    }
			});
		}

    function resetForm(context) {
				context.find('input[name="name"]').val('');
        context.find('input[name="identifier"]').val('');
        context.find('input:checkbox[name|="pushIn"]').prop('checked', false);
				context.find('input[name="pushInUrl"]').val('');
        context.find('input[name="pushInSecret"]').val('');
        context.find('input:checkbox[name|="pushOut"]').prop('checked', false);
				context.find('input[name="pushOutUrl"]').val('');
				context.find('input[name="pushOutSecret"]').val('');
				context.find('input:checkbox[name|="pushNeutral"]').prop('checked', false);
				context.find('input[name="pushNeutralUrl"]').val('');
        context.find('input[name="pushNeutralSecret"]').val('');
    }

		</script>

  </head>

  <body>
		<div class="navbar navbar-inverse navbar-static-top">
			<div class="container">
				<span class="navbar-brand" style="color:#FFF;"><strong>PesaPi</strong> - Mobile money middleware - configuration tool</span>
			</div>
    </div>

		<!-- BEGIN DYNAMIC BLOCK: Setup_credentials -->
		<div class="container">
			<div class="alert alert-danger">
				<h1>Missing credentials</h1>
				<p>PesaPi is not able to connect to the database!<br/>
				Most likely you have not yet configured the correct values in: <strong>include/PLUSPEOPLE/PesaPi/Configuration.php</strong></p>
				<br/>
				<button type="button" class="btn btn-success" onClick="document.location.reload()"><span class="glyphicon glyphicon-refresh"></span> Retry</button>
			</div>
		</div>
		<!-- END DYNAMIC BLOCK: Setup_credentials -->

		<!-- BEGIN DYNAMIC BLOCK: Setup_database -->
		<div class="container">
			<div class="alert alert-warning">
				<h1>Missing database structure</h1>
				<p>The PesaPi database structure/tables are not created in the database you have configured the system to use!<br/>
					</p>
				<br/>
				<button type="button" class="btn btn-success" onClick="createDb();"><span class="glyphicon glyphicon-flash"></span> Create the db-structure automatically</button>
				<button type="button" class="btn btn-default" onClick="document.location.reload()"><span class="glyphicon glyphicon-refresh"></span> Retry</button>
			</div>
		</div>
		<!-- END DYNAMIC BLOCK: Setup_database -->

		<!-- BEGIN DYNAMIC BLOCK: Setup_ok -->
		<div class="container">
			<div class="col-md-6">
				<h3>Choose account type to configure</h3>
        <hr width="99%"/>
		    <h4>Private account types</h4>
		
        <!-- KE: MPESA PRIVATE -->
				<div class="panel panel-primary">
					<div class="panel-heading" onclick="$(this).next().slideToggle();">
						<h3 class="panel-title">Kenya: Mpesa private / Lipa na Mpesa buygoods</h3>
					</div>
					<div class="panel-body" style="display:none;">
						<p>This option is for private/personal Mpesa accounts or for "Lipa na Mpesa" (buy goods) accounts. In short if the only way you are "notified" about transactions is through SMS's from Mpesa then this is the type to use.<br/>
							Notice you will need to install SMS-Sync on an android phone to forward the SMS's to PesaPi</p>
		        <input type="hidden" name="type" value="2" />

						<div class="input-group">
							<span class="input-group-addon" style="min-width:80px;">Name</span>
							<input type="text" class="form-control" name="name" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:80px;">Identifier</span>
							<input type="text" class="form-control" name="identifier" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushIn" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money come in" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushInDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushInUrl" placeholder="http://www.yourdomain.com/in.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushInSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushOut" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushOutDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushOutUrl" placeholder="http://www.yourdomain.com/out.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushOutSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushNeutral" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification on actions where money neither come in or go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushNeutralDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushNeutralUrl" placeholder="http://www.yourdomain.com/neutral.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushNeutralSecret" placeholder="" />
							</div>
						</div>

						<div class="pull-right" style="margin-top:10px;">
							<button type="button" class="btn btn-success" onClick="addPrivateAccount($(this).parent().parent());">Register Account</button>
						</div>

					</div>
				</div>



        <!-- KE: AIRTEL PRIVATE -->
				<div class="panel panel-primary">
					<div class="panel-heading" onclick="$(this).next().slideToggle();">
						<h3 class="panel-title">Kenya: Airtel private</h3>
					</div>
					<div class="panel-body" style="display:none;">
						<p>This option is for private/personal Airtel money accounts. In short if the only way you are "notified" about transactions is through SMS's from Airtel money then this is the type to use.<br/>
							Notice you will need to install SMS-Sync on an android phone to forward the SMS's to PesaPi</p>
		        <input type="hidden" name="type" value="8" />

						<div class="input-group">
							<span class="input-group-addon" style="min-width:80px;">Name</span>
							<input type="text" class="form-control" name="name" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:80px;">Identifier</span>
							<input type="text" class="form-control" name="identifier" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushIn" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money come in" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushInDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushInUrl" placeholder="http://www.yourdomain.com/in.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushInSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushOut" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushOutDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushOutUrl" placeholder="http://www.yourdomain.com/out.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushOutSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushNeutral" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification on actions where money neither come in or go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushNeutralDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushNeutralUrl" placeholder="http://www.yourdomain.com/neutral.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushNeutralSecret" placeholder="" />
							</div>
						</div>

						<div class="pull-right" style="margin-top:10px;">
							<button type="button" class="btn btn-success" onClick="addPrivateAccount($(this).parent().parent());">Register Account</button>
						</div>

					</div>
				</div>




        <!-- TZ: MPESA PRIVATE -->
				<div class="panel panel-primary">
					<div class="panel-heading" onclick="$(this).next().slideToggle();">
						<h3 class="panel-title">Tanzania: Mpesa private</h3>
					</div>
					<div class="panel-body" style="display:none;">
						<p>This option is for private/personal Mpesa accounts in Tanzania. If the only way you are "notified" about transactions is through SMS's from Mpesa then this is the type to use.<br/>
							Notice you will need to install SMS-Sync on an android phone to forward the SMS's to PesaPi</p>
		        <input type="hidden" name="type" value="6" />

						<div class="input-group">
							<span class="input-group-addon" style="min-width:80px;">Name</span>
							<input type="text" class="form-control" name="name" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:80px;">Identifier</span>
							<input type="text" class="form-control" name="identifier" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushIn" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money come in" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushInDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushInUrl" placeholder="http://www.yourdomain.com/in.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushInSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushOut" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushOutDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushOutUrl" placeholder="http://www.yourdomain.com/out.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushOutSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushNeutral" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification on actions where money neither come in or go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushNeutralDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushNeutralUrl" placeholder="http://www.yourdomain.com/neutral.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushNeutralSecret" placeholder="" />
							</div>
						</div>

						<div class="pull-right" style="margin-top:10px;">
							<button type="button" class="btn btn-success" onClick="addPrivateAccount($(this).parent().parent());">Register Account</button>
						</div>
					</div>
				</div>


        <!-- TZ: TIGO PRIVATE -->
				<div class="panel panel-primary">
					<div class="panel-heading" onclick="$(this).next().slideToggle();">
						<h3 class="panel-title">Tanzania: Tigo private</h3>
					</div>
					<div class="panel-body" style="display:none;">
						<p>This option is for private/personal TIGO money accounts in Tanzania. If the only way you are "notified" about transactions is through SMS's from TIGO then this is the type to use.<br/>
							Notice you will need to install SMS-Sync on an android phone to forward the SMS's to PesaPi</p>
		        <input type="hidden" name="type" value="7" />

						<div class="input-group">
							<span class="input-group-addon" style="min-width:80px;">Name</span>
							<input type="text" class="form-control" name="name" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:80px;">Identifier</span>
							<input type="text" class="form-control" name="identifier" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushIn" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money come in" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushInDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushInUrl" placeholder="http://www.yourdomain.com/in.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushInSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushOut" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushOutDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushOutUrl" placeholder="http://www.yourdomain.com/out.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushOutSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushNeutral" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification on actions where money neither come in or go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushNeutralDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushNeutralUrl" placeholder="http://www.yourdomain.com/neutral.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushNeutralSecret" placeholder="" />
							</div>
						</div>

						<div class="pull-right" style="margin-top:10px;">
							<button type="button" class="btn btn-success" onClick="addPrivateAccount($(this).parent().parent());">Register Account</button>
						</div>
					</div>
				</div>


        <!-- UG: MTN PRIVATE -->
				<div class="panel panel-primary">
					<div class="panel-heading" onclick="$(this).next().slideToggle();">
						<h3 class="panel-title">Uganda: MTN private</h3>
					</div>
					<div class="panel-body" style="display:none;">
						<p>This option is for private/personal MTN money accounts in Uganda. If the only way you are "notified" about transactions is through SMS's from MTN then this is the type to use.<br/>
							Notice you will need to install SMS-Sync on an android phone to forward the SMS's to PesaPi</p>
		        <input type="hidden" name="type" value="15" />

						<div class="input-group">
							<span class="input-group-addon" style="min-width:80px;">Name</span>
							<input type="text" class="form-control" name="name" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:80px;">Identifier</span>
							<input type="text" class="form-control" name="identifier" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushIn" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money come in" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushInDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushInUrl" placeholder="http://www.yourdomain.com/in.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushInSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushOut" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushOutDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushOutUrl" placeholder="http://www.yourdomain.com/out.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushOutSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushNeutral" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification on actions where money neither come in or go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushNeutralDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushNeutralUrl" placeholder="http://www.yourdomain.com/neutral.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushNeutralSecret" placeholder="" />
							</div>
						</div>

						<div class="pull-right" style="margin-top:10px;">
							<button type="button" class="btn btn-success" onClick="addPrivateAccount($(this).parent().parent());">Register Account</button>
						</div>
					</div>
				</div>

        <!-- GH: AIRTEL PRIVATE -->
				<div class="panel panel-primary">
					<div class="panel-heading" onclick="$(this).next().slideToggle();">
						<h3 class="panel-title">Ghana: Airtel private</h3>
					</div>
					<div class="panel-body" style="display:none;">
						<p>This option is for private/personal Airtel money accounts in Ghana. If the only way you are "notified" about transactions is through SMS's from Airtel then this is the type to use.<br/>
							Notice you will need to install SMS-Sync on an android phone to forward the SMS's to PesaPi</p>
		        <input type="hidden" name="type" value="4" />

						<div class="input-group">
							<span class="input-group-addon" style="min-width:80px;">Name</span>
							<input type="text" class="form-control" name="name" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:80px;">Identifier</span>
							<input type="text" class="form-control" name="identifier" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushIn" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money come in" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushInDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushInUrl" placeholder="http://www.yourdomain.com/in.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushInSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushOut" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushOutDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushOutUrl" placeholder="http://www.yourdomain.com/out.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushOutSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushNeutral" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification on actions where money neither come in or go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushNeutralDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushNeutralUrl" placeholder="http://www.yourdomain.com/neutral.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushNeutralSecret" placeholder="" />
							</div>
						</div>

						<div class="pull-right" style="margin-top:10px;">
							<button type="button" class="btn btn-success" onClick="addPrivateAccount($(this).parent().parent());">Register Account</button>
						</div>
					</div>
				</div>


        <!-- GH: MTN PRIVATE -->
				<div class="panel panel-primary">
					<div class="panel-heading" onclick="$(this).next().slideToggle();">
						<h3 class="panel-title">Ghana: MTN private</h3>
					</div>
					<div class="panel-body" style="display:none;">
						<p>This option is for private/personal MTN money accounts in Ghana. If the only way you are "notified" about transactions is through SMS's from MTN then this is the type to use.<br/>
							Notice you will need to install SMS-Sync on an android phone to forward the SMS's to PesaPi</p>
		        <input type="hidden" name="type" value="13" />

						<div class="input-group">
							<span class="input-group-addon" style="min-width:80px;">Name</span>
							<input type="text" class="form-control" name="name" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:80px;">Identifier</span>
							<input type="text" class="form-control" name="identifier" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushIn" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money come in" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushInDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushInUrl" placeholder="http://www.yourdomain.com/in.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushInSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushOut" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushOutDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushOutUrl" placeholder="http://www.yourdomain.com/out.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushOutSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushNeutral" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification on actions where money neither come in or go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushNeutralDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushNeutralUrl" placeholder="http://www.yourdomain.com/neutral.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushNeutralSecret" placeholder="" />
							</div>
						</div>

						<div class="pull-right" style="margin-top:10px;">
							<button type="button" class="btn btn-success" onClick="addPrivateAccount($(this).parent().parent());">Register Account</button>
						</div>
					</div>
				</div>


        <!-- RW: MTN PRIVATE -->
				<div class="panel panel-primary">
					<div class="panel-heading" onclick="$(this).next().slideToggle();">
						<h3 class="panel-title">Rwanda: MTN private</h3>
					</div>
					<div class="panel-body" style="display:none;">
						<p>This option is for private/personal MTN money accounts in Rwanda. If the only way you are "notified" about transactions is through SMS's from MTN then this is the type to use.<br/>
							Notice you will need to install SMS-Sync on an android phone to forward the SMS's to PesaPi</p>
		        <input type="hidden" name="type" value="5" />

						<div class="input-group">
							<span class="input-group-addon" style="min-width:80px;">Name</span>
							<input type="text" class="form-control" name="name" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:80px;">Identifier</span>
							<input type="text" class="form-control" name="identifier" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushIn" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money come in" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushInDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushInUrl" placeholder="http://www.yourdomain.com/in.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushInSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushOut" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushOutDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushOutUrl" placeholder="http://www.yourdomain.com/out.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushOutSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushNeutral" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification on actions where money neither come in or go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushNeutralDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushNeutralUrl" placeholder="http://www.yourdomain.com/neutral.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushNeutralSecret" placeholder="" />
							</div>
						</div>

						<div class="pull-right" style="margin-top:10px;">
							<button type="button" class="btn btn-success" onClick="addPrivateAccount($(this).parent().parent());">Register Account</button>
						</div>
					</div>
				</div>

        <!-- SO: GOLIS PRIVATE -->
				<div class="panel panel-primary">
					<div class="panel-heading" onclick="$(this).next().slideToggle();">
						<h3 class="panel-title">Somalia: Golis Sahal private</h3>
					</div>
					<div class="panel-body" style="display:none;">
						<p>This option is for private/personal Sahal money accounts in Somalia. If the only way you are "notified" about transactions is through SMS's from Sahal then this is the type to use.<br/>
							Notice you will need to install SMS-Sync on an android phone to forward the SMS's to PesaPi</p>
		        <input type="hidden" name="type" value="10" />

						<div class="input-group">
							<span class="input-group-addon" style="min-width:80px;">Name</span>
							<input type="text" class="form-control" name="name" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:80px;">Identifier</span>
							<input type="text" class="form-control" name="identifier" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushIn" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money come in" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushInDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushInUrl" placeholder="http://www.yourdomain.com/in.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushInSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushOut" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushOutDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushOutUrl" placeholder="http://www.yourdomain.com/out.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushOutSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushNeutral" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification on actions where money neither come in or go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushNeutralDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushNeutralUrl" placeholder="http://www.yourdomain.com/neutral.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushNeutralSecret" placeholder="" />
							</div>
						</div>

						<div class="pull-right" style="margin-top:10px;">
							<button type="button" class="btn btn-success" onClick="addPrivateAccount($(this).parent().parent());">Register Account</button>
						</div>
					</div>
				</div>


        <!-- CO: MPESA PRIVATE -->
				<div class="panel panel-primary">
					<div class="panel-heading" onclick="$(this).next().slideToggle();">
						<h3 class="panel-title">Congo: MPESA private</h3>
					</div>
					<div class="panel-body" style="display:none;">
						<p>This option is for private/personal MPESA money accounts in Congo. If the only way you are "notified" about transactions is through SMS's from Vodaphone/Mpesa then this is the type to use.<br/>
							Notice you will need to install SMS-Sync on an android phone to forward the SMS's to PesaPi</p>
		        <input type="hidden" name="type" value="14" />

						<div class="input-group">
							<span class="input-group-addon" style="min-width:80px;">Name</span>
							<input type="text" class="form-control" name="name" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:80px;">Identifier</span>
							<input type="text" class="form-control" name="identifier" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushIn" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money come in" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushInDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushInUrl" placeholder="http://www.yourdomain.com/in.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushInSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushOut" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushOutDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushOutUrl" placeholder="http://www.yourdomain.com/out.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushOutSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushNeutral" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification on actions where money neither come in or go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushNeutralDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushNeutralUrl" placeholder="http://www.yourdomain.com/neutral.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushNeutralSecret" placeholder="" />
							</div>
						</div>

						<div class="pull-right" style="margin-top:10px;">
							<button type="button" class="btn btn-success" onClick="addPrivateAccount($(this).parent().parent());">Register Account</button>
						</div>
					</div>
				</div>


        <hr width="99%"/>
		    <h4>Commercial account types</h4>

        <!-- KE: MPESA PAYBILL -->
				<div class="panel panel-success">
					<div class="panel-heading" onclick="$(this).next().slideToggle();">
						<h3 class="panel-title">Kenya: Mpesa paybill</h3>
					</div>
					<div class="panel-body" style="display:none;">
						<p>This option is for Mpesa <strong>commercial</strong> accounts in Kenya. You need to have obtained a paybill acount from Safaricom before you can use this type.<br/>
							IF you do are unsure if what you have is a paybill - then it is NOT!<br/>
							Notice you need to install the Certificate issued by Vodaphone on your server/hosting to get this working.<br/>
		        <input type="hidden" name="type" value="1" />

						<div class="input-group">
							<span class="input-group-addon" style="min-width:125px;">Name</span>
							<input type="text" class="form-control" name="name" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:125px;">Paybill no.</span>
							<input type="text" class="form-control" name="identifier" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:125px;">Certificate path</span>
							<input type="text" class="form-control" name="certificate" placeholder="" />
						</div>
						<small>Here you enter the absolute path to the PEM certificate on the server</small>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:125px;">Organisation</span>
							<input type="text" class="form-control" name="organisation" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:125px;">Login Name</span>
							<input type="text" class="form-control" name="login" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon" style="min-width:125px;">Password</span>
							<input type="password" class="form-control" name="password" placeholder="" />
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushIn" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money come in" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushInDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushInUrl" placeholder="http://www.yourdomain.com/in.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushInSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushOut" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification when money go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushOutDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushOutUrl" placeholder="http://www.yourdomain.com/out.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushOutSecret" placeholder="" />
							</div>
						</div>

						<div class="input-group" style="margin-top:10px;">
							<span class="input-group-addon">
								<input type="checkbox" name="pushNeutral" value="1" onclick="$(this).parent().parent().next().slideToggle();"/>
							</span>
							<input type="text" class="form-control" value="PUSH notification on actions where money neither come in or go out" disabled="disabled"/>
						</div>
						<div style="display:none;" id="pushNeutralDetails">
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">URL</span>
								<input type="text" class="form-control" name="pushNeutralUrl" placeholder="http://www.yourdomain.com/neutral.php" />
							</div>
							<div class="input-group" style="margin-top:10px;margin-left:40px;">
								<span class="input-group-addon" style="min-width:80px;">Secret</span>
								<input type="text" class="form-control" name="pushNeutralSecret" placeholder="" />
							</div>
						</div>

						<div class="pull-right" style="margin-top:10px;">
							<button type="button" class="btn btn-success" onClick="addMpesaPaybill($(this).parent().parent());">Register Account</button>
						</div>


					</div>
				</div>

			</div>


			<div class="col-md-6" id="accountsList">
		    <!-- BEGIN DYNAMIC BLOCK: Accounts_wrap -->
    		<h3>Accounts already configured</h3>
        <hr width="99%"/>

				<div>
					<!-- BEGIN DYNAMIC BLOCK: Account -->
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">{NAME}</h3>
						</div>
						<div class="panel-body">
							<table class="table table-bordered">
								<tr>
									<td>Type</td>
									<td>{TYPE}</td>
								</tr>
								<tr>
									<td>Identifier</td>
									<td>{IDENTIFIER}</td>
								</tr>
								<tr>
									<td>PUSH Incomming</td>
									<td>{PUSH_IN}</td>
								</tr>
								<tr>
									<td>PUSH Outgoing</td>
									<td>{PUSH_OUT}</td>
								</tr>
								<tr>
									<td>PUSH Neutral</td>
									<td>{PUSH_NEUTRAL}</td>
								</tr>
								
								<!-- BEGIN DYNAMIC BLOCK: Account_smssync -->
								<tr>
									<th colspan="2" class="text-center">SMS-Sync settings</th>
								</tr>
								<tr>
									<td>Sync URL</td>
									<td>http://{DOMAIN}/smssync.php?identifier={IDENTIFIER}</td>
								</tr>
								<tr>
									<td>Sync secret</td>
									<td>{SYNC_SECRET}</td>
								</tr>
								<!-- END DYNAMIC BLOCK: Account_smssync -->

								<!-- BEGIN DYNAMIC BLOCK: Account_mpesa_paybill -->
								<tr>
									<th colspan="2" class="text-center">Paybill specific</th>
								</tr>
								<tr>
									<td>Certificate
										<!-- BEGIN DYNAMIC BLOCK: Account_mpesa_paybill_certificate_test -->
										<br/><button type="button" class="btn btn-success" onClick="testPaybillCertificate('{IDENTIFIER}');"><span class="glyphicon glyphicon-question-sign"></span> Test certificate</button>
										<!-- END DYNAMIC BLOCK: Account_mpesa_paybill_certificate_test -->
									</td>
									<td>{CERTIFICATE}
										<!-- BEGIN DYNAMIC BLOCK: Account_mpesa_paybill_certificate_exists -->
										<br/><span style="color:#0F0;">File exists &amp; is readable</span>
										<!-- END DYNAMIC BLOCK: Account_mpesa_paybill_certificate_exists -->
										<!-- BEGIN DYNAMIC BLOCK: Account_mpesa_paybill_certificate_exists_not -->
										<br/><span style="color:#F00;">FILE DOES NOT EXIST OR PATH IS INCORRECT</span>
										<!-- END DYNAMIC BLOCK: Account_mpesa_paybill_certificate_exists_not -->
									</td>
								</tr>
								<tr>
									<td>Organisation</td>
									<td>{ORGANISATION}</td>
								</tr>
								<tr>
									<td>Login</td>
									<td>{LOGIN}</td>
								</tr>
								<tr>
									<td>Password</td>
									<td>{PASSWORD}</td>
								</tr>
								<tr>
									<td>IPN Url</td>
									<td>http://{DOMAIN}/mpesaIPN.php</td>
								</tr>
								<!-- END DYNAMIC BLOCK: Account_mpesa_paybill -->
								

							</table>
						</div>
					</div>
					<!-- END DYNAMIC BLOCK: Account -->
					<!-- END DYNAMIC BLOCK: Account_wrap -->
				</div>
			</div>
		</div>
		<!-- END DYNAMIC BLOCK: Setup_ok -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
		<script type="text/javascript">
	  $().ready(function() {
			updateAccounts();
		});
    </script>
  </body>
</html>
