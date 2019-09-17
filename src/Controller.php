<?php

namespace Saraiva\Framework;

use Saraiva\Framework\Entity\User;
use Exception;
use Saraiva\Framework\Ajax\Response;
use Saraiva\Framework\Exception\FormProcessModelException;

class Controller {

    /**
     *
     * @var Database\Connection
     */
    protected $connection;
    protected $appname;

    /**
     *
     * @var View
     */
    protected $view;
    protected $requireLoggedIn = TRUE;
    /**
     *
     * @var User
     */
    protected $user;

    public function __construct(Database\Connection $connection, $appname, View $view) {
        $this->connection = $connection;
        $this->appname = $appname;
        $this->view = $view;
        $this->_requireLoggedIn();
    }

    protected function _requireLoggedIn() {
        if ($this->requireLoggedIn) {
            if (!Security\Authentication::isLoggedIn()) {
                header("Location: /Login/login");
                exit();
            }
        }
    }
    
    public function _setLoggedInUser(User $user) {
        $this->user = $user;
    }

    protected function processFormInput(Form $form, $input, $return = FALSE) {
        $erros = $form->process($input);

        if (count($erros)) {
            $result = Response::response(FALSE, 'Ocorreram erros de validação', array('erros' => $erros));
            
            if ($return) {
                return $result;
            } else {
                echo $result;
                return FALSE;
            }
        }
        
        return TRUE;
    }
    
    protected function processFormExecute(Form $form, $input, $return = FALSE) {
        if (FALSE === $this->processFormInput($form, $input)) {
            return FALSE;
        }
        
        try {
            $result = $form->execute($this->connection);

            if ($return) {
                return $result;
            }

            $msg = array(
                'msg' => 'Dados salvos',
                'data' => $result,
            );
            echo Response::response(TRUE, 'Dados salvos', $msg);
            return TRUE;
        } catch (FormProcessModelException $ex) {
            echo Response::response(FALSE, $ex->getMessage(), array('erros' => $ex->getErros()));
            return FALSE;
        } catch (Exception $ex) {
            echo Response::responseException($ex->getMessage());
            return FALSE;
        }
    }

}
