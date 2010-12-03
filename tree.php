<?php
class com_meego_comments_tree
{
    public static map_comment_to_tree(com_meego_comments_comment $comment, array &$tree)
    {
        $comment->subcomments = array();

        if (!$comment->up)
        {
            $tree[$comment->id] = $comment;
            return;
        }

        foreach ($tree as $id => $parent)
        {
            if ($comment->up == $id)
            {
                $parent->subcomments[$comment->id] = $comment;
                return;
            }

            if (count($parent->subcomments) > 0)
            {
                // Look at the next level
                $this->map_comment_to_tree($comment, $parent->subcomments);
            }
        }
    }
}
