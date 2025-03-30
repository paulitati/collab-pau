<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Tareas */

$this->title = 'Crear actividad'." ". $tipoactividad ;
$this->params['breadcrumbs'][] = ['label' => 'Tareas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tareas-create">

<h2 class="perfil-title"><?= Html::encode($this->title) ?><span>.</span></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'asigid'=>$asigid,
        'asignaturaYear'=>$asignaturaYear,
    ]) ?>

</div>
