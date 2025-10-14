<?php
declare(strict_types=1);

namespace App\Controller\System;

use App\Controller\System\SystemController;

/**
 * Hospitals Controller
 *
 * @property \App\Model\Table\HospitalsTable $Hospitals
 */
class HospitalsController extends SystemController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Hospitals->find();
        
        // Handle search filter
        if ($this->request->getQuery('search')) {
            $search = $this->request->getQuery('search');
            $query->where([
                'OR' => [
                    'Hospitals.name LIKE' => '%' . $search . '%',
                    'Hospitals.subdomain LIKE' => '%' . $search . '%'
                ]
            ]);
        }
        
        // Handle status filter
        if ($this->request->getQuery('status')) {
            $status = $this->request->getQuery('status');
            $query->where(['Hospitals.status' => $status]);
        }
        
        // Order by name
        $query->order(['Hospitals.name' => 'ASC']);
        
        // Paginate results
        $hospitals = $this->paginate($query, [
            'limit' => 15
        ]);
        
        // Get filter counts for badges
        $totalCount = $this->Hospitals->find()->count();
        $activeCount = $this->Hospitals->find()->where(['status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])->count();
        $inactiveCount = $this->Hospitals->find()->where(['status' => SiteConstants::HOSPITAL_STATUS_INACTIVE])->count();
        
        $this->set(compact('hospitals', 'totalCount', 'activeCount', 'inactiveCount'));
    }

    /**
     * View method
     *
     * @param string|null $id Hospital id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $hospital = $this->Hospitals->get($id, [
            'contain' => ['Users']
        ]);
        
        // Get user counts by role for this hospital
        $userCounts = $this->Hospitals->Users->find()
            ->select([
                'role_type' => 'Roles.type',
                'count' => $this->Hospitals->Users->find()->func()->count('Users.id')
            ])
            ->contain(['Roles'])
            ->where(['Users.hospital_id' => $id])
            ->group(['Roles.type'])
            ->toArray();
        
        $this->set(compact('hospital', 'userCounts'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $hospital = $this->Hospitals->newEmptyEntity();
        if ($this->request->is('post')) {
            $hospital = $this->Hospitals->patchEntity($hospital, $this->request->getData());
            if ($this->Hospitals->save($hospital)) {
                $this->Flash->success(__('The hospital has been saved successfully.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The hospital could not be saved. Please, try again.'));
        }
        $this->set(compact('hospital'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Hospital id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $hospital = $this->Hospitals->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $hospital = $this->Hospitals->patchEntity($hospital, $this->request->getData());
            if ($this->Hospitals->save($hospital)) {
                $this->Flash->success(__('The hospital has been updated successfully.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The hospital could not be saved. Please, try again.'));
        }
        $this->set(compact('hospital'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Hospital id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $hospital = $this->Hospitals->get($id);
        
        // Check if hospital has users
        $userCount = $this->Hospitals->Users->find()->where(['hospital_id' => $id])->count();
        
        if ($userCount > 0) {
            $this->Flash->error(__('Cannot delete hospital. It has {0} associated users. Please reassign or delete users first.', $userCount));
            return $this->redirect(['action' => 'index']);
        }
        
        if ($this->Hospitals->delete($hospital)) {
            $this->Flash->success(__('The hospital has been deleted successfully.'));
        } else {
            $this->Flash->error(__('The hospital could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Toggle Status method
     *
     * @param string|null $id Hospital id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function toggleStatus($id = null)
    {
        $this->request->allowMethod(['post']);
        $hospital = $this->Hospitals->get($id);
        
        // Toggle status
        $hospital->status = ($hospital->status === SiteConstants::HOSPITAL_STATUS_ACTIVE) ? SiteConstants::HOSPITAL_STATUS_INACTIVE : SiteConstants::HOSPITAL_STATUS_ACTIVE;
        
        if ($this->Hospitals->save($hospital)) {
            $status = ucfirst($hospital->status);
            $this->Flash->success(__('Hospital has been {0} successfully.', strtolower($status)));
        } else {
            $this->Flash->error(__('Could not update hospital status. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}