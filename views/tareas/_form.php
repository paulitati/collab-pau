<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Tareas */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tareas-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'nombre_t')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'consigna')->textarea() ?>

    <?= $form->field($model, 'descripcion')->textarea() ?>

    <?= $form->field($model, 'year')->textInput(['value' => $asignaturaYear, 'readonly' => true]) ?>
    
    <?= $form->field($model, 'usar_sentencias_apertura')->radioList(['1' => 'Sí', '0' => 'No']) ?>
    
    <?= $form->field($model, 'reportar_estado_animo')->radioList(['1' => 'Sí', '0' => 'No']) ?>
    
    <?= $form->field($model, 'reportar_conflicto')->radioList(['1' => 'Sí', '0' => 'No']) ?>
    
    <?= $form->field($model, 'actividad_gamificada')->radioList(['1' => 'Sí', '0' => 'No']) ?>

    <div id="puntaje-tarea-container" style="display: none;">
        <?= $form->field($model, 'puntaje_tarea')->textInput(['type' => 'number']) ?>
        <p>Nota: Asigna un puntaje estimativo según el grado de dificultad de la tarea. Generalmente, 
        <span style="font-weight:600; color:#FD8916;">un valor entre 100 y 1000.</span></p>
    </div>

    <?= $form->field($model, 'grupos_id')->dropDownList(app\models\Grupos::getListaGrupos($asigid)) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'button-g2']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
    // Lógica para mostrar/ocultar el campo puntaje_tarea según la selección de actividad_gamificada
    document.addEventListener('DOMContentLoaded', function () {
        const actividadGamificadaRadios = document.querySelectorAll('input[name="Tareas[actividad_gamificada]"]');
        const puntajeTareaContainer = document.getElementById('puntaje-tarea-container');

        function togglePuntajeTarea() {
            const selectedRadio = document.querySelector('input[name="Tareas[actividad_gamificada]"]:checked');
            if (selectedRadio && selectedRadio.value === '1') {
                puntajeTareaContainer.style.display = 'block';
            } else {
                puntajeTareaContainer.style.display = 'none';
            }
        }

        // Inicializar al cargar la página
        togglePuntajeTarea();

        // Escuchar cambios en los radios
        actividadGamificadaRadios.forEach(radio => {
            radio.addEventListener('change', togglePuntajeTarea);
        });
    });
</script>
