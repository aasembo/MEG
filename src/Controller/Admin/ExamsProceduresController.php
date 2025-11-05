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
        // Configure pagination options
        $this->paginate = [
            'sortableFields' => [
                'ExamsProcedures.id',
                'ExamsProcedures.exam_id',
                'ExamsProcedures.procedure_id',
                'ExamsProcedures.contrast_required',
                'ExamsProcedures.sedation_required',
                'ExamsProcedures.created',
                'ExamsProcedures.modified',
                'Exams.name',
                'Procedures.name'
            ],
            'order' => ['ExamsProcedures.id' => 'desc'],
            'limit' => 25
        ];
        
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        // Build query with filters
        $query = $this->ExamsProcedures->find()
            ->contain(['Exams', 'Procedures'])
            ->matching('Exams', function ($q) use ($hospitalId) {
                return $q->where(['Exams.hospital_id' => $hospitalId]);
            });
        
        // Apply search filter
        if ($this->request->getQuery('search')) {
            $search = $this->request->getQuery('search');
            $query->where([
                'OR' => [
                    'Exams.name LIKE' => '%' . $search . '%',
                    'Procedures.name LIKE' => '%' . $search . '%'
                ]
            ]);
        }
        
        // Apply exam filter
        if ($this->request->getQuery('exam_id')) {
            $query->where(['ExamsProcedures.exam_id' => $this->request->getQuery('exam_id')]);
        }
        
        // Apply procedure filter
        if ($this->request->getQuery('procedure_id')) {
            $query->where(['ExamsProcedures.procedure_id' => $this->request->getQuery('procedure_id')]);
        }
        
        // Apply contrast required filter
        if ($this->request->getQuery('contrast_required') !== null && $this->request->getQuery('contrast_required') !== '') {
            $query->where(['ExamsProcedures.contrast_required' => (bool)$this->request->getQuery('contrast_required')]);
        }
        
        // Apply sedation required filter
        if ($this->request->getQuery('sedation_required') !== null && $this->request->getQuery('sedation_required') !== '') {
            $query->where(['ExamsProcedures.sedation_required' => (bool)$this->request->getQuery('sedation_required')]);
        }
            
        $examsProcedures = $this->paginate($query);
        
        // Get options for filters
        $exams = $this->ExamsProcedures->Exams->find('list', [
            'conditions' => ['hospital_id' => $hospitalId],
            'order' => ['name' => 'ASC']
        ])->toArray();
        
        $procedures = $this->ExamsProcedures->Procedures->find('list', [
            'conditions' => ['hospital_id' => $hospitalId],
            'order' => ['name' => 'ASC']
        ])->toArray();
        
        $this->set(compact('examsProcedures', 'exams', 'procedures'));
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