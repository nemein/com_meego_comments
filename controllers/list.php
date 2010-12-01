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
        $qb = com_meego_comments_comment::new_query_builder();
        $qb->add_constraint('to', '=', $args['to']);
        $qb->add_order('metadata.published', 'ASC');
        $comments = $qb->execute();
        foreach ($comments as $comment)
        {
            $this->data['comments'][] = $comment;
        }
    }
}
