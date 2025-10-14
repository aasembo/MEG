<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

class ExamsProceduresController extends AppController {
    public function initialize(): void {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function index() {
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        $query = $this->ExamsProcedures->find()
            ->contain(['Exams', 'Procedures'])
            ->matching('Exams', function ($q) use ($hospitalId) {
                return $q->where(['Exams.hospital_id' => $hospitalId]);
            });
            
        $examsProcedures = $this->paginate($query);
        
        $this->set(compact('examsProcedures'));
    }

    public function view($id = null) {
        $examsProcedure = $this->ExamsProcedures->get($id, [
            'contain' => ['Exams', 'Procedures'],
        ]);
        
        $this->set(compact('examsProcedure'));
    }

    public function add() {
        $examsProcedure = $this->ExamsProcedures->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $examsProcedure = $this->ExamsProcedures->patchEntity($examsProcedure, $this->request->getData());
            
            if ($this->ExamsProcedures->save($examsProcedure)) {
                $this->Flash->success(__('The exam-procedure association has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The exam-procedure association could not be saved. Please, try again.'));
        }
        
        // Get hospital-specific options
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        $exams = $this->ExamsProcedures->Exams->find('list', [
            'conditions' => ['hospital_id' => $hospitalId]
        ])->all();
        $procedures = $this->ExamsProcedures->Procedures->find('list', [
            'conditions' => ['hospital_id' => $hospitalId]
        ])->all();
        
        $this->set(compact('examsProcedure', 'exams', 'procedures'));
    }

    public function edit($id = null) {
        $examsProcedure = $this->ExamsProcedures->get($id, [
            'contain' => [],
        ]);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $examsProcedure = $this->ExamsProcedures->patchEntity($examsProcedure, $this->request->getData());
            if ($this->ExamsProcedures->save($examsProcedure)) {
                $this->Flash->success(__('The exam-procedure association has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The exam-procedure association could not be saved. Please, try again.'));
        }
        
        // Get hospital-specific options
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        $exams = $this->ExamsProcedures->Exams->find('list', [
            'conditions' => ['hospital_id' => $hospitalId]
        ])->all();
        $procedures = $this->ExamsProcedures->Procedures->find('list', [
            'conditions' => ['hospital_id' => $hospitalId]
        ])->all();
        
        $this->set(compact('examsProcedure', 'exams', 'procedures'));
    }

    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $examsProcedure = $this->ExamsProcedures->get($id);
        
        if ($this->ExamsProcedures->delete($examsProcedure)) {
            $this->Flash->success(__('The exam-procedure association has been deleted.'));
        } else {
            $this->Flash->error(__('The exam-procedure association could not be deleted. Please, try again.'));
        }
        
        return $this->redirect(['action' => 'index']);
    }
}