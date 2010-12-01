<?php
class com_meego_comments_controllers_comment extends midgardmvc_core_controllers_baseclasses_crud
{
    public function load_object(array $args)
    {
        $this->object = new com_meego_comments_comment($args['comment']);
    }
    
    public function prepare_new_object(array $args)
    {
        $this->data['parent'] = midgard_object_class::get_object_by_guid($args['to']);
        $this->object = new com_meego_comments_comment();
        $this->object->objectguid = $this->data['parent']->guid;
    }

    public function load_form()
    {
        $this->form = midgardmvc_helper_forms::create('com_meego_comments_comment');
        $this->form->set_action
        (
            midgardmvc_core::get_instance()->dispatcher->generate_url
            (
                'comment_create', array
                (
                    'to' => $this->data['parent']->guid
                ),
                $this->request
            )
        );

        // Basic element information
        $field = $this->form->add_field('content', 'text');
        $field->set_value($this->object->content);
        $widget = $field->set_widget('textarea');
        $widget->set_placeholder('Write your comment here');
    }

    public function get_url_read()
    {
        return midgardmvc_core::get_instance()->dispatcher->generate_url
        (
            'list_comments', array
            (
                'to' => $this->object->objectguid
            ),
            $this->request
        );
    }
    
    public function get_url_update()
    {
        return midgardmvc_core::get_instance()->dispatcher->generate_url
        (
            'comment_update', array
            (
                'comment' => $this->object->guid
            ),
            $this->request
        );
    }
}
