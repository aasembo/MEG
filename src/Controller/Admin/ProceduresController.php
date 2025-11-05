<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

class ProceduresController extends AppController {
    public function initialize(): void {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function index() {
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        $query = $this->Procedures->find()
            ->contain(['Hospitals', 'Sedations', 'Departments'])
            ->where(['Procedures.hospital_id' => $hospitalId]);
            
        // Handle search
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Procedures.name LIKE' => '%' . $search . '%',
                    'Procedures.description LIKE' => '%' . $search . '%',
                    'Procedures.type LIKE' => '%' . $search . '%',
                    'Procedures.notes LIKE' => '%' . $search . '%'
                ]
            ]);
        }
        
        // Configure pagination with sortable fields
        $this->paginate = [
            'limit' => 25,
            'order' => ['Procedures.name' => 'ASC'],
            'sortableFields' => [
                'Procedures.name',
                'Procedures.type',
                'Procedures.created',
                'Procedures.modified',
                'Procedures.duration_minutes',
                'Procedures.cost',
                'Procedures.risk_level',
                'Departments.name'
            ]
        ];
            
        $procedures = $this->paginate($query);
        
        $this->set(compact('procedures'));
    }

    public function view($id = null) {
        $procedure = $this->Procedures->get($id, [
            'contain' => ['Hospitals', 'Sedations', 'Departments', 'Exams'],
        ]);
        
        $this->set(compact('procedure'));
    }

    public function add() {
        $procedure = $this->Procedures->newEmptyEntity();
        
        if ($this->request->is('post')) {
            // Set hospital context
            $currentHospital = $this->request->getSession()->read('Hospital.current');
            $hospitalId = $currentHospital ? $currentHospital->id : 1;
            
            $data = $this->request->getData();
            $data['hospital_id'] = $hospitalId;
            
            $procedure = $this->Procedures->patchEntity($procedure, $data);
            
            if ($this->Procedures->save($procedure)) {
                $this->Flash->success(__('The procedure has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The procedure could not be saved. Please, try again.'));
        }
        
        // Get hospital-specific options
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        $hospitals = $this->Procedures->Hospitals->find('list', ['limit' => 200])->all();
        $sedations = $this->Procedures->Sedations->find('list', [
            'conditions' => ['hospital_id' => $hospitalId]
        ])->all();
        $departments = $this->Procedures->Departments->find('list', [
            'conditions' => ['hospital_id' => $hospitalId]
        ])->all();
        
        $this->set(compact('procedure', 'hospitals', 'sedations', 'departments'));
    }

    public function edit($id = null) {
        $procedure = $this->Procedures->get($id, [
            'contain' => [],
        ]);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $procedure = $this->Procedures->patchEntity($procedure, $this->request->getData());
            if ($this->Procedures->save($procedure)) {
                $this->Flash->success(__('The procedure has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The procedure could not be saved. Please, try again.'));
        }
        
        // Get hospital-specific options
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        $hospitals = $this->Procedures->Hospitals->find('list', ['limit' => 200])->all();
        $sedations = $this->Procedures->Sedations->find('list', [
            'conditions' => ['hospital_id' => $hospitalId]
        ])->all();
        $departments = $this->Procedures->Departments->find('list', [
            'conditions' => ['hospital_id' => $hospitalId]
        ])->all();
        
        $this->set(compact('procedure', 'hospitals', 'sedations', 'departments'));
    }
    
    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $procedure = $this->Procedures->get($id);
        
        if ($this->Procedures->delete($procedure)) {
            $this->Flash->success(__('The procedure has been deleted.'));
        } else {
            $this->Flash->error(__('The procedure could not be deleted. Please, try again.'));
        }
        
        return $this->redirect(['action' => 'index']);
    }
}