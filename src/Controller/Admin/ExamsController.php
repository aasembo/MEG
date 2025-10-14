<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

class ExamsController extends AppController {
    public function initialize(): void {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function index() {
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        $query = $this->Exams->find()
            ->contain(['Hospitals', 'Modalities', 'Departments'])
            ->where(['Exams.hospital_id' => $hospitalId]);
            
        $exams = $this->paginate($query);
        
        $this->set(compact('exams'));
    }

    public function view($id = null) {
        $exam = $this->Exams->get($id, [
            'contain' => ['Hospitals', 'Modalities', 'Departments', 'ExamsProcedures', 'ExamsProcedures.Procedures'],
        ]);
        
        $this->set(compact('exam'));
    }

    public function add() {
        $exam = $this->Exams->newEmptyEntity();
        
        if ($this->request->is('post')) {
            // Set hospital context
            $currentHospital = $this->request->getSession()->read('Hospital.current');
            $hospitalId = $currentHospital ? $currentHospital->id : 1;
            
            $data = $this->request->getData();
            $data['hospital_id'] = $hospitalId;
            
            $exam = $this->Exams->patchEntity($exam, $data);
            
            if ($this->Exams->save($exam)) {
                $this->Flash->success(__('The exam has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The exam could not be saved. Please, try again.'));
        }
        
        // Get hospital-specific options
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        $hospitals = $this->Exams->Hospitals->find('list', ['limit' => 200])->all();
        $modalities = $this->Exams->Modalities->find('list', [
            'conditions' => ['hospital_id' => $hospitalId]
        ])->all();
        $departments = $this->Exams->Departments->find('list', [
            'conditions' => ['hospital_id' => $hospitalId]
        ])->all();
        
        $this->set(compact('exam', 'hospitals', 'modalities', 'departments'));
    }

    public function edit($id = null) {
        $exam = $this->Exams->get($id, [
            'contain' => [],
        ]);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $exam = $this->Exams->patchEntity($exam, $this->request->getData());
            if ($this->Exams->save($exam)) {
                $this->Flash->success(__('The exam has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The exam could not be saved. Please, try again.'));
        }
        
        // Get hospital-specific options
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        $hospitals = $this->Exams->Hospitals->find('list', ['limit' => 200])->all();
        $modalities = $this->Exams->Modalities->find('list', [
            'conditions' => ['hospital_id' => $hospitalId]
        ])->all();
        $departments = $this->Exams->Departments->find('list', [
            'conditions' => ['hospital_id' => $hospitalId]
        ])->all();
        
        $this->set(compact('exam', 'hospitals', 'modalities', 'departments'));
    }
    
    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $exam = $this->Exams->get($id);
        
        if ($this->Exams->delete($exam)) {
            $this->Flash->success(__('The exam has been deleted.'));
        } else {
            $this->Flash->error(__('The exam could not be deleted. Please, try again.'));
        }
        
        return $this->redirect(['action' => 'index']);
    }
}