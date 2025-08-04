<?php
namespace app\Controller;

use app\Core\Controller;

/**
 * Class TagController
 *
 * Provides a simple listing of all tags used throughout the application and
 * the number of tasks associated with each tag. Users can click on a tag
 * to view tasks filtered by that tag via the task list view.
 */
class TagController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $taskModel = $this->loadModel('Task');
        $tags = $taskModel->getAllTags();
        $this->render('tag/index', ['tags' => $tags]);
    }
}