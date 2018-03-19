<div class="container">
	<!--
		Название сайта и ссылка на создание статей
	-->
	<div class="row align-items-center mt-3 mb-3">
	<div class="col-md-8">
		<a class="h4" href="/">Блог по веб-программированию</a>
	</div>
	<div class="col-md-4">
		<?php if($auth->isAuthorized()): //not guest?>
		<a class="btn btn-info" href="newarticle.php?action=random">Random</a>
		<a class="btn btn-dark float-right" href="newarticle.php">Написать</a>
		<?php else: ?>
		<a class="btn btn-dark float-right" href="login.php">Регистрация</a>
		<a class="btn btn-info float-right mr-2" href="register.php">Войти</a>
		<?php endif; ?>
	</div>
	</div>
</div>
<hr>