<?php

namespace Saraiva\Framework;

use Saraiva\Framework\Entity\User;
use Saraiva\Framework\View\Twig\Filter\maskcpfcnpj;
use Twig\Extension\CoreExtension;
use Twig\Extension\DebugExtension;
use Twig\TwigFilter;
use Twig_Environment;
use Twig_Loader_Filesystem;

class View {
    
    protected $path;
    protected $appname;
    protected $vars = array();
    /**
     *
     * @var Twig_Environment
     */
    public $twig;
    protected $TEMPLATE_TITLE = '';
    protected $TEMPLATE_BREADCRUMBS = array();
    protected $USER = NULL;
    
    public function __construct($template_path, $public_path) {
        $loader = new Twig_Loader_Filesystem($template_path);
        $this->twig = new Twig_Environment($loader, array(
            'debug' => TRUE,
        ));
        
        $this->twig->addExtension(new DebugExtension());
        
        // adiciona filtros
        $this->twig->addFilter(new TwigFilter('maskcpfcnpj', array(maskcpfcnpj::class, 'maskcpfcnpj')));
        
        // define padroes
        $this->twig->getExtension(CoreExtension::class)->setNumberFormat(2, ',', '.');
        
        View\Functions\Filemtime::setTwigFunction($this->twig, $public_path);
    }
    
    public function assign($name, $value) {
        $this->vars[$name] = $value;
    }
    
    private function _render($view) {
        $title = 'voeDH';
        if ($this->TEMPLATE_TITLE !== '') {
            $title .= ' - ' . $this->TEMPLATE_TITLE;
        } elseif (count($this->TEMPLATE_BREADCRUMBS)) {
            $title .= ' - ' . implode(' - ', $this->TEMPLATE_BREADCRUMBS);
        }
        $this->addGlobal('TEMPLATE', array(
            'title' => $title,
        ));
        return $this->twig->render($view, $this->vars);
    }
    
    public function render($view, $return = FALSE) {
        $result = $this->_render($view);
        if ($return) {
            return $result;
        } else {
            echo $result;
            return;
        }
    }
    
    public function addGlobal($name, $value) {
        $this->twig->addGlobal($name, $value);
    }
    
    public function setMenuContents($page_nav, User $user) {
        $filter = array();
        
        $this->filterMenu($page_nav, $filter, $user);
        
        $this->twig->addGlobal('APP_PAGE_NAV', $filter);
    }
    
    private function filterMenu($page_nav, &$filter, User $user) {
        foreach ($page_nav as $k => $v) {
            if (isset($v['acl'])) {
                if (TRUE === Security\AccessControl::hasPermission($v['acl']['class'], $v['acl']['required'])) {
                    $filter[$k] = $v;
                }
                continue;
            }
            
            if (isset($v['callback']) && is_callable($v['callback'])) {
                if (FALSE === call_user_func_array($v['callback'], [$user])) {
                    continue;
                }
            }
            
            if (isset($v['sub'])) {
                $sub = array();
                $this->filterMenu($v['sub'], $sub, $user);
                
                if (count($sub)) {
                    $v['sub'] = $sub;
                    $filter[$k] = $v;
                }
                continue;
            }
            
            $filter[$k] = $v;
        }
    }
    
    public function setUserContents($id, $name, $email, $avatar = '') {
        if ($avatar == '') { $avatar = 'sunny.png'; }
        $this->twig->addGlobal('_LOGGEDUSER', array(
            'name' => $name,
            'id' => $id,
            'email' => $email,
            'avatar' => $avatar
        ));
    }
    
    public function setPageTitle($title) {
        $this->TEMPLATE_TITLE = $title;
    }
    
    public function addBreadcrumbSegment($segment) {
        $this->TEMPLATE_BREADCRUMBS[] = $segment;
    }
}
