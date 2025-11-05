<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

class SedationsController extends AppController {
    public function initialize(): void {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function index() {
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        $query = $this->Sedations->find()
            ->contain(['Hospitals'])
            ->where(['Sedations.hospital_id' => $hospitalId]);
        
        // Handle search
        $searchTerm = $this->request->getQuery('search');
        if (!empty($searchTerm)) {
            $query->where([
                'OR' => [
                    'Sedations.level LIKE' => '%' . $searchTerm . '%',
                    'Sedations.type LIKE' => '%' . $searchTerm . '%',
                    'Sedations.description LIKE' => '%' . $searchTerm . '%',
                    'Sedations.medications LIKE' => '%' . $searchTerm . '%'
                ]
            ]);
        }
        
        // Handle sort dropdown - apply to query directly
        $sortParam = $this->request->getQuery('sort');
        if (!empty($sortParam)) {
            switch ($sortParam) {
                case 'level':
                    $query->orderAsc('Sedations.level');
                    break;
                case 'level DESC':
                    $query->orderDesc('Sedations.level');
                    break;
                case 'created':
                    $query->orderAsc('Sedations.created');
                    break;
                case 'created DESC':
                    $query->orderDesc('Sedations.created');
                    break;
                default:
                    $query->orderDesc('Sedations.created');
                    break;
            }
        } else {
            $query->orderDesc('Sedations.created');
        }
        
        // Set pagination options
        $this->paginate = [
            'limit' => 25,
            'sortableFields' => [
                'Sedations.level',
                'Sedations.type', 
                'Sedations.created'
            ]
        ];
            
        $sedations = $this->paginate($query);
        
        $this->set(compact('sedations'));
    }

    public function view($id = null) {
        $sedation = $this->Sedations->get($id, [
            'contain' => ['Hospitals', 'Procedures'],
        ]);
        
        $this->set(compact('sedation'));
    }

    public function add() {
        $sedation = $this->Sedations->newEmptyEntity();
        
        if ($this->request->is('post')) {
            // Set hospital context
            $currentHospital = $this->request->getSession()->read('Hospital.current');
            $hospitalId = $currentHospital ? $currentHospital->id : 1;
            
            $data = $this->request->getData();
            $data['hospital_id'] = $hospitalId;
            
            $sedation = $this->Sedations->patchEntity($sedation, $data);
            
            if ($this->Sedations->save($sedation)) {
                $this->Flash->success(__('The sedation has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The sedation could not be saved. Please, try again.'));
        }
        
        $hospitals = $this->Sedations->Hospitals->find('list', ['limit' => 200])->all();
        $this->set(compact('sedation', 'hospitals'));
    }

    public function edit($id = null) {
        $sedation = $this->Sedations->get($id, [
            'contain' => [],
        ]);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $sedation = $this->Sedations->patchEntity($sedation, $this->request->getData());
            if ($this->Sedations->save($sedation)) {
                $this->Flash->success(__('The sedation has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The sedation could not be saved. Please, try again.'));
        }
        
        $hospitals = $this->Sedations->Hospitals->find('list', ['limit' => 200])->all();
        $this->set(compact('sedation', 'hospitals'));
    }
    
    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $sedation = $this->Sedations->get($id);
        
        if ($this->Sedations->delete($sedation)) {
            $this->Flash->success(__('The sedation has been deleted.'));
        } else {
            $this->Flash->error(__('The sedation could not be deleted. Please, try again.'));
        }
        
        return $this->redirect(['action' => 'index']);
    }
}