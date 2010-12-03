<?php
class com_meego_comments_controllers_list
{
    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
    }

    public function get_comments(array $args)
    {
        $this->data['to'] = midgard_object_class::get_object_by_guid($args['to']);
        if (!$this->data['to'])
        {
            throw new midgardmvc_exception_notfound("Comment target not found");
        }

        $this->data['comments'] = array();

        $storage = new midgard_query_storage('com_meego_comments_comment');
        $q = new midgard_query_select($storage);
        $q->set_constraint
        (
            new midgard_query_constraint
            (
                new midgard_query_property('to', $storage),
                '=',
                new midgard_query_value($this->data['to']->guid)
            )
        );

        $q->add_order(new midgard_query_property('metadata.created', $storage), SORT_ASC);
        $q->execute();
        $comments = $q->list_objects();

        foreach ($comments as $comment)
        {
            $this->data['comments'][] = $comment;
        }

        if (midgardmvc_core::get_instance()->authentication->is_user())
        {
            $this->data['can_post'] = true;
        }
        else
        {
            $this->data['can_post'] = false;
        }
    }

    public function get_comments_tree(array $args)
    {
        $this->get_comments($args);
        $comments = $this->data['comments'];
        $this->data['comments'] = array();
        foreach ($comments as $comment)
        {
            com_meego_comments_tree::map_comment_to_tree($comment, $this->data['comments']);
        }
    }
}
