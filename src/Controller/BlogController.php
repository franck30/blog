<?php


namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BlogController
 * @package App\Controller
 */
class BlogController extends AbstractController
{

    /**
     * @\Symfony\Component\Routing\Annotation\Route("/", name="index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response {
//        $total = $this->getDoctrine()->getRepository(Post::class)->count([]);
        $limit = $request->get("limit", 10);
        $page = $request->get("page", 1);

        /** @var Paginator $posts */
        $posts = $this->getDoctrine()->getRepository(Post::class)->getPaginatedPosts(
            $page,
            $limit
        );

        $pages = ceil($posts->count() / 10);

        $range = range(
            max($page - 3, 1),
            min($page + 3, $pages)
        );

        return $this->render("index.html.twig", [
            "posts" => $posts,
            "pages" => $pages,
            "page" => $page,
            "range" => $range,
            "limit" => $limit
        ]);
    }

    /**
     * @\Symfony\Component\Routing\Annotation\Route("/article-{id}", name="blog_read")
     * @param Post $post
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function read(Post $post, Request $request): Response {

        $comment = new Comment();
        $comment->setPost($post);
        $form = $this->createForm(CommentType::class, $comment)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($comment);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute("blog_read", ["id" => $post->getId()]);
        }
        return $this->render("read.html.twig", [
            "post" => $post,
            "form" => $form->createView()
        ]);
    }

    /**
     * @\Symfony\Component\Routing\Annotation\Route("/publier-article", name="blog_create")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function create(Request $request): Response
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($post);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute("blog_read", ["id" => $post->getId()]);
        }


        return $this->render("create.html.twig", [
            "form" => $form->createView()
        ]);
    }

}
