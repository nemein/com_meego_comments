<?php
class com_meego_comments_controllers_comment extends midgardmvc_core_controllers_baseclasses_crud
{
    private $mvc = null;
    private $relocate = null;

    public function __construct(midgardmvc_core_request $request)
    {
        parent::__construct($request);
        $this->mvc = midgardmvc_core::get_instance();
    }

    public function load_object(array $args)
    {
        $this->object = new com_meego_comments_comment($args['comment']);
    }

    public function prepare_new_object(array $args)
    {
        $this->data['parent'] = midgard_object_class::get_object_by_guid($args['to']);
        $this->object = new com_meego_comments_comment();
        $this->object->to = $this->data['parent']->guid;
    }

    /**
     * Loads a form. The action (POST) depends on the purpose
     * @param string can be "update" or anything else
     */
    public function load_form($purpose = null)
    {
        $this->form = midgardmvc_helper_forms::create('com_meego_comments_comment');

        switch ($purpose)
        {
            case 'update':
                $action = $this->mvc->dispatcher->generate_url
                (
                    'comment_update', array
                    (
                        'comment' => $this->object->guid
                    ),
                    $this->request
                );
                break;
            case 'delete':
                $action = $this->mvc->dispatcher->generate_url
                (
                    'comment_delete', array
                    (
                        'comment' => $this->object->guid
                    ),
                    $this->request
                );
                break;
            default:
                $action = $this->mvc->dispatcher->generate_url
                (
                    'comment_create', array
                    (
                        'to' => $this->data['parent']->guid
                    ),
                    $this->request
                );
        }

        $this->form->set_action($action);

        if (array_key_exists('relocate', $_GET))
        {
            $field = $this->form->add_field('relocate', 'text', false);
            $field->set_value($_GET['relocate']);
            $field->set_widget('hidden');
        }
        else if ($this->request->is_subrequest())
        {
            // Comment posting form is in a dynamic_load, set parent URL for redirects
            $root_request = $this->mvc->context->get_request(0);
            $field = $this->form->add_field('relocate', 'text', false);
            $field->set_value($root_request->get_path());
            $field->set_widget('hidden');
        }

        // Basic element information
        $field = $this->form->add_field('content', 'text');
        $field->set_value($this->object->content);
        $widget = $field->set_widget('textarea');
        $widget->set_css('content');
        $widget->set_placeholder('Write your comment here');
    }

    public function process_form()
    {
        $this->form->process_post();
        $this->object->content = $this->form->content->get_value();

        if (isset($this->form->relocate))
        {
            $this->relocate = $this->form->relocate->get_value();
        }
        else if (array_key_exists('relocate', $_GET))
        {
            $this->relocate = $_GET['relocate'];
        }
    }

    public function get_delete(array $args)
    {
        $this->load_object($args);
        $this->data['object'] =& $this->object;
        $this->mvc->authorization->require_do('midgard:delete', $this->object);

        $this->load_form('delete');
        $this->form->set_readonly(true);
        $this->data['form'] =& $this->form;
    }

    public function post_delete(array $args)
    {
        $this->get_delete($args);

        if (isset($_POST['delete']))
        {
            $transaction = new midgard_transaction();
            $transaction->begin();
            $this->object->delete();
            $transaction->commit();

            $this->mvc->cache->invalidate(array($this->object->guid));

            if (array_key_exists('relocate', $_GET))
            {
                $this->mvc->head->relocate($_GET['relocate']);
            }
            else
            {
                $this->mvc->head->relocate($this->mvc->context->get_request()->get_prefix());
            }
        }
    }

    public function get_update(array $args)
    {
        $this->load_object($args);
        $this->data['object'] =& $this->object;
        $this->mvc->authorization->require_do('midgard:update', $this->object);

        $this->load_form('update');
        $this->data['form'] =& $this->form;
    }

    public function get_url_read()
    {
        return $this->mvc->dispatcher->generate_url
        (
            'comment_read', array
            (
                'comment' => $this->object->guid
            ),
            $this->request
        );
    }

    public function get_url_update()
    {
        return $this->mvc->dispatcher->generate_url
        (
            'comment_update', array
            (
                'comment' => $this->object->guid
            ),
            $this->request
        );
    }

    public function relocate_to_read()
    {
        if (!is_null($this->relocate))
        {
            $this->mvc->head->relocate($this->relocate);
        }
        $this->mvc->head->relocate($this->get_url_read());
    }
}
