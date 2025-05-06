<?php

namespace app\controllers;

use Yii;
use app\models\Tareas;
use app\models\Evento;
use app\models\TareasSearch;
use app\models\Preguntas;
use app\models\MultipleChoice;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\data\ActiveDataProvider;



/**
 * TareasController implements the CRUD actions for Tareas model.
 */
class TareasController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'update', 'delete', 'create'],
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'update', 'create', 'delete'],
                        'allow' => true,
                        'roles' => ['profesor'],
                    ],
                    [
                        'actions' => ['index', 'view', 'update', 'delete', 'create'],
                        'allow' => true,
                        'roles' => ['administrador'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Tareas models.
     * @return mixed
     */
    public function actionIndex($asigid)
    {
        $usuario = Yii::$app->user->identity->id;
        $oUser = \app\models\Usuarios::findOne(['id' => $usuario]);
        $asigid = Yii::$app->security->decryptByPassword($asigid, $oUser->password);
    
        $searchModel = new TareasSearch();
        $searchModel->asignaturas_id = $asigid;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    
        $modelEvento = new Evento();
        $tareas = Tareas::find()->where(['asignaturas_id' => $asigid])->all();
    
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'asigid' => $asigid,
            'modelEvento' => $modelEvento,
            'tareas' => $tareas,
        ]);
    }
    

    public function actionCreateEvento($asigid)
    {
        $modelEvento = new Evento();
    
        if ($modelEvento->load(Yii::$app->request->post())) {
            $modelEvento->imagen = UploadedFile::getInstance($modelEvento, 'imagen');
            if ($modelEvento->imagen) {
                $filePath = 'uploads/' . $modelEvento->imagen->baseName . '.' . $modelEvento->imagen->extension;
                $modelEvento->imagen->saveAs($filePath);
                $modelEvento->imagen = $filePath;
            }
    
            if ($modelEvento->save()) {
                Yii::$app->session->setFlash('success', 'Evento creado con éxito.');
                return $this->asJson(['success' => true]);
            } else {
                return $this->asJson(['success' => false, 'errors' => $modelEvento->errors]);
            }
        }
    
        return $this->asJson(['success' => false, 'errors' => 'No data was provided.']);
    }
    
    
    
    public function actionTareasAlumnos($asigid, $year) {      
        $userid = Yii::$app->user->identity->id;   
        $oUser = \app\models\Usuarios::findOne(['id' => $userid]);
        $asigid_decoded = Yii::$app->security->decryptByPassword($asigid, $oUser->password);
        $year_decoded = Yii::$app->security->decryptByPassword($year, $oUser->password);
        
        
        $searchModel = new TareasSearch();
        $searchModel->asignaturas_id = $asigid_decoded;
        $searchModel->year = $year_decoded;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('tareas-alumnos', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'asigid' => $asigid_decoded,
        ]);
    }

    /**
     * Displays a single Tareas model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        $modelEvento = new Evento();

        return $this->render('view', [
                    'model' => $this->findModel($id),
                    'modelEvento' => $modelEvento,
        ]);
    }

    /**
     * Creates a new Tareas model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
   public function actionCreate($asigid, $tipoactividad) {
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    // Consulta a la tabla Asignaturas para obtener el nombre y año
    $asignatura = \app\models\Asignaturas::findOne(['id' => $asigid]);
    if ($asignatura) {
        $asignaturaYear = $asignatura->year;
    } else {
        Yii::$app->session->setFlash('error', 'La asignatura no existe.');
        return $this->redirect(['index']); // Redirigir a index en caso de error
    }

    
    if ($tipoactividad == 'grupal') {
        $model = new Tareas();
        $model->asignaturas_id = $asigid;
        $model->tipo_tarea = 'grupal';

        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $grupos = \app\models\GruposFormados::getDetalleGrupos($model->grupos_id);
            $titulo = "";

            foreach ($grupos as $gr) {
                if ($titulo != $gr["nombre"]) {
                    $objChat = new \app\models\Chats();
                    $objChat->descripcion = 'Chat correspondiente a la tarea ' . $model->descripcion . ' que emplea la configuración de grupos ' . $gr['codigo'];
                    $objChat->fecha = date('Y-m-d h:i:s', time());
                    $objChat->tareas_id = $model->id;
                    $objChat->grupos_formados_id = $gr['id'];
                    $objChat->save();
                    $titulo = $gr["nombre"];
                }
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'tipoactividad' => $tipoactividad,
            'asigid'=>$asigid,
            'asignaturaYear'=>$asignaturaYear,
        ]);

    } elseif ($tipoactividad == 'individual') {
        $model = new Tareas();
        $model->asignaturas_id = $asigid;
        $model->tipo_tarea = 'individual';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $grupos = \app\models\GruposFormados::getDetalleGrupos($model->grupos_id);
            $titulo = "";

            foreach ($grupos as $gr) {
                if ($titulo != $gr["nombre"]) {
                    $objChat = new \app\models\Chats();
                    $objChat->descripcion = 'Chat correspondiente a la tarea ' . $model->descripcion . ' que emplea la configuración de grupos ' . $gr['codigo'];
                    $objChat->fecha = date('Y-m-d h:i:s', time());
                    $objChat->tareas_id = $model->id;
                    $objChat->grupos_formados_id = $gr['id'];
                    $objChat->save();
                    $titulo = $gr["nombre"];
                }
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'tipoactividad' => $tipoactividad,
            'asignaturaYear'=>$asignaturaYear,
        ]);

    } elseif ($tipoactividad == 'cuestionarioevaluativo') {
        // Manejo del Cuestionario Evaluativo
        $modelTarea = new Tareas();
        $modelTarea->asignaturas_id = $asigid;
        $modelTarea->tipo_tarea = 'cuestionarioevaluativo';
        $preguntas = [new \app\models\Preguntas()];  // Crear el modelo de las preguntas
        $multipleChoice = [new \app\models\MultipleChoice()];  // Crear el modelo de multiple choice si es necesario

        if ($modelTarea->load(Yii::$app->request->post()) && $modelTarea->save()) {
            // Guardar las preguntas
            $preguntasPost = Yii::$app->request->post('Preguntas', []);
            foreach ($preguntasPost as $index => $preguntaData) {
                $pregunta = new \app\models\Preguntas();
                $pregunta->tarea_id = $modelTarea->id;
                $pregunta->pregunta = $preguntaData['pregunta'];
                $pregunta->es_multiple_choice = $preguntaData['es_multiple_choice'];
                $pregunta->archivo = UploadedFile::getInstance($pregunta, "[$index]archivo");

                if ($pregunta->save()) {
                    // Guardar opciones multiple choice si es necesario
                    if ($pregunta->es_multiple_choice) {
                        $multipleChoicePost = Yii::$app->request->post('MultipleChoice', []);
                        foreach ($multipleChoicePost[$index] as $opcionData) {
                            $opcion = new \app\models\MultipleChoice();
                            $opcion->pregunta_id = $pregunta->id;
                            $opcion->opcion = $opcionData['opcion'];
                            $opcion->es_correcta = $opcionData['es_correcta'];
                            $opcion->save();
                        }
                    }
                }
            }

            Yii::$app->session->setFlash('success', 'Cuestionario evaluativo creado con éxito.');
            return $this->redirect(['view', 'id' => $modelTarea->id]);
        }

        return $this->render('crear-cuestionario', [
            'modelTarea' => $modelTarea,
            'preguntas' => $preguntas,
            'multipleChoice' => $multipleChoice,
            'asignaturaYear'=>$asignaturaYear,
        ]);
    }
}


    /**
     * Updates an existing Tareas model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);
        $model->usar_sentencias_apertura = ($model->usar_sentencias_apertura) ? 1 : 0;

        $asignatura = \app\models\Asignaturas::findOne(['id' => $model->asignaturas_id]);
        if ($asignatura) {
            $asignaturaYear = $asignatura->year;
            $asigid=$asignatura->id;
        } else {
            Yii::$app->session->setFlash('error', 'La asignatura no existe.');
            return $this->redirect(['index']); // Redirigir a index en caso de error
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
                    'asignaturaYear'=>$asignaturaYear,
                    'asigid'=>$asigid,

        ]);
    }

    /**
     * Deletes an existing Tareas model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['asignaturas/index']);
    }

    /**
     * Finds the Tareas model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Tareas the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */



    protected function findModel($id) {
        if (($model = Tareas::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /* Entorno gamificado*/
    //Permite elegir el tipo de actividad a crear.
    //Renderiza la vista elegir-actividad.
    public function actionElegirActividad($asigid) {
        return $this->render('elegir-actividad', [
            'asigid' => $asigid,
        ]);
    }

    //Crea un cuestionario individual.
    //Maneja la creación de preguntas y opciones de multiple choice.
    //Renderiza la vista crear-cuestionario
    public function actionCrearCuestionarioIndividual()
    {
        // Crear modelos de la tarea y las preguntas
        $modelTarea = new Tareas();
        $preguntas = [new Preguntas()];
        $multipleChoice = [new MultipleChoice()];

        if ($modelTarea->load(Yii::$app->request->post()) && $modelTarea->save()) {

            $preguntasPost = Yii::$app->request->post('Preguntas', []);
            foreach ($preguntasPost as $index => $preguntaData) {
                $pregunta = new Preguntas();
                $pregunta->tarea_id = $modelTarea->id;
                $pregunta->pregunta = $preguntaData['pregunta'];
                $pregunta->es_multiple_choice = $preguntaData['es_multiple_choice'];
                $pregunta->archivo = UploadedFile::getInstance($pregunta, "[$index]archivo");

                if ($pregunta->save()) {
                    // Guardar opciones multiple choice si existe
                    if ($pregunta->es_multiple_choice) {
                        $multipleChoicePost = Yii::$app->request->post('MultipleChoice', []);
                        foreach ($multipleChoicePost[$index] as $opcionData) {
                            $opcion = new MultipleChoice();
                            $opcion->pregunta_id = $pregunta->id;
                            $opcion->opcion = $opcionData['opcion'];
                            $opcion->es_correcta = $opcionData['es_correcta'];
                            $opcion->save();
                        }
                    }
                }
            }
            
            Yii::$app->session->setFlash('success', 'Cuestionario creado con éxito.');
            return $this->redirect(['view', 'id' => $modelTarea->id]);
        }

        return $this->render('crear-cuestionario', [
            'modelTarea' => $modelTarea,
            'preguntas' => $preguntas,
            'multipleChoice' => $multipleChoice,
        ]);
    }

   


}
