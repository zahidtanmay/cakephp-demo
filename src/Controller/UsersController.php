<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Auth\DefaultPasswordHasher;
use App\Traits\JwtToken;
use Cake\Validation\Validator;
/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    use JwtToken;

    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Paginator');
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $users = $this->paginate($this->Users);
        $this->set('meta',current($this->request->getAttribute('paging')));
        $this->set('users', $users);
        $this->viewBuilder()->setOption('serialize', ['users', 'meta']);
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->request->allowMethod(['post', 'put']);
        $user = $this->Users->newEntity($this->request->getData());
        if ($user->hasErrors()) {
            $errors = array_merge(...array_values($user->getErrors()));
            $errors = array_values($errors);

            throw new BadRequestException(json_encode($errors));
        } else {
            $this->Users->save($user);
        }
        $this->set([
            'message' => 'User Added successfully',
            'user' => $user,
        ]);
        $this->viewBuilder()->setOption('serialize', ['user', 'message']);
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function login(){
        $this->request->allowMethod(['post']);
        $req = $this->request->getData();
        $email = isset($req['email']) ? $req['email'] : '';
        $password = isset($req['password']) ? $req['password'] : '';

        if($email && $password) {
            $user = $this->Users->findByEmail($email)->first();
            if($user) {
                if((new DefaultPasswordHasher)->check($password, $user['password'])){
                    $token = $this->jwt($user['id']);
                    $this->set(compact('user', 'token'));
                    $this->set('_serialize', array('user', 'token'));
                } else {
                    throw new UnauthorizedException('Incorrect password');
                }
            } else {

                throw new UnauthorizedException('User does not exists');
            }

        } else {
            if (!$email) {
                throw new BadRequestException('Email not provided');
            }

            if (!$password) {
                throw new BadRequestException('Password not provided');
            }

        }

    }
}
