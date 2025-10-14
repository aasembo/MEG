<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

class DepartmentsController extends AppController {
    public function initialize(): void {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function index() {
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        $query = $this->Departments->find()
            ->contain(['Hospitals'])
            ->where(['Departments.hospital_id' => $hospitalId]);
            
        $departments = $this->paginate($query);
        
        $this->set(compact('departments'));
    }

    public function view($id = null) {
        $department = $this->Departments->get($id, [
            'contain' => ['Hospitals', 'Exams', 'Procedures'],
        ]);
        
        $this->set(compact('department'));
    }

    public function add() {
        $department = $this->Departments->newEmptyEntity();
        
        if ($this->request->is('post')) {
            // Set hospital context
            $currentHospital = $this->request->getSession()->read('Hospital.current');
            $hospitalId = $currentHospital ? $currentHospital->id : 1;
            
            $data = $this->request->getData();
            $data['hospital_id'] = $hospitalId;
            
            $department = $this->Departments->patchEntity($department, $data);
            
            if ($this->Departments->save($department)) {
                $this->Flash->success(__('The department has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The department could not be saved. Please, try again.'));
        }
        
        $hospitals = $this->Departments->Hospitals->find('list', ['limit' => 200])->all();
        $this->set(compact('department', 'hospitals'));
    }

    public function edit($id = null) {
        $department = $this->Departments->get($id, [
            'contain' => [],
        ]);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $department = $this->Departments->patchEntity($department, $this->request->getData());
            if ($this->Departments->save($department)) {
                $this->Flash->success(__('The department has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The department could not be saved. Please, try again.'));
        }
        
        $hospitals = $this->Departments->Hospitals->find('list', ['limit' => 200])->all();
        $this->set(compact('department', 'hospitals'));
    }
    
    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $department = $this->Departments->get($id);
        
        if ($this->Departments->delete($department)) {
            $this->Flash->success(__('The department has been deleted.'));
        } else {
            $this->Flash->error(__('The department could not be deleted. Please, try again.'));
        }
        
        return $this->redirect(['action' => 'index']);
    }
}