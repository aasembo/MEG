<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

class ModalitiesController extends AppController {
    public function initialize(): void {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function index() {
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $currentHospital ? $currentHospital->id : 1;
        
        $query = $this->Modalities->find()
            ->contain(['Hospitals'])
            ->where(['Modalities.hospital_id' => $hospitalId]);
        
        // Handle search
        $searchTerm = $this->request->getQuery('search');
        if (!empty($searchTerm)) {
            $query->where([
                'OR' => [
                    'Modalities.name LIKE' => '%' . $searchTerm . '%',
                    'Modalities.description LIKE' => '%' . $searchTerm . '%'
                ]
            ]);
        }
        
        // Handle sort dropdown - apply to query directly
        $sortParam = $this->request->getQuery('sort');
        if (!empty($sortParam)) {
            switch ($sortParam) {
                case 'name':
                    $query->orderAsc('Modalities.name');
                    break;
                case 'name DESC':
                    $query->orderDesc('Modalities.name');
                    break;
                case 'created':
                    $query->orderAsc('Modalities.created');
                    break;
                case 'created DESC':
                    $query->orderDesc('Modalities.created');
                    break;
                default:
                    $query->orderDesc('Modalities.created');
                    break;
            }
        } else {
            $query->orderDesc('Modalities.created');
        }
        
        // Set pagination options
        $this->paginate = [
            'limit' => 25,
            'sortableFields' => [
                'Modalities.name',
                'Modalities.description', 
                'Modalities.created'
            ]
        ];
            
        $modalities = $this->paginate($query);
        
        $this->set(compact('modalities'));
    }

    public function view($id = null) {
        $modality = $this->Modalities->get($id, [
            'contain' => ['Hospitals', 'Exams'],
        ]);
        
        $this->set(compact('modality'));
    }

    public function add() {
        $modality = $this->Modalities->newEmptyEntity();
        
        if ($this->request->is('post')) {
            // Set hospital context
            $currentHospital = $this->request->getSession()->read('Hospital.current');
            $hospitalId = $currentHospital ? $currentHospital->id : 1;
            
            $data = $this->request->getData();
            $data['hospital_id'] = $hospitalId;
            
            $modality = $this->Modalities->patchEntity($modality, $data);
            
            if ($this->Modalities->save($modality)) {
                $this->Flash->success(__('The modality has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The modality could not be saved. Please, try again.'));
        }
        
        $hospitals = $this->Modalities->Hospitals->find('list', ['limit' => 200])->all();
        $this->set(compact('modality', 'hospitals'));
    }

    public function edit($id = null) {
        $modality = $this->Modalities->get($id, [
            'contain' => [],
        ]);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $modality = $this->Modalities->patchEntity($modality, $this->request->getData());
            if ($this->Modalities->save($modality)) {
                $this->Flash->success(__('The modality has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The modality could not be saved. Please, try again.'));
        }
        
        $hospitals = $this->Modalities->Hospitals->find('list', ['limit' => 200])->all();
        $this->set(compact('modality', 'hospitals'));
    }
    
    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $modality = $this->Modalities->get($id);
        
        if ($this->Modalities->delete($modality)) {
            $this->Flash->success(__('The modality has been deleted.'));
        } else {
            $this->Flash->error(__('The modality could not be deleted. Please, try again.'));
        }
        
        return $this->redirect(['action' => 'index']);
    }
}