<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/post", name="post.")
 */

class PostController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @param PostRepository $postRepository
     * @return Response
     */
    public function index(PostRepository $postRepository)
    {
        $posts = $postRepository->findAll();


        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        $form->getErrors();

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();

            $file = $request->files->get('post')['attachment'];

            /** @var UploadedFile $file */
            if($file){
                $filename = md5(uniqid()).'.'. $file->guessClientExtension();

                $file->move(
                    $this->getParameter('uploads_dir'),
                    $filename
                );

                $post->setImage($filename);
            }

            $em->persist($post);

            $em->flush();

            return $this->redirect($this->generateUrl('post.index'));
        }


        return $this->render('post/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/show/{id}", name="show")
     * @param $id
     * @param PostRepository $postRepository
     * @return Response
     */
    public function show($id, PostRepository $postRepository)
    {
        $post = $postRepository->findPostWithCategory($id);


        return $this->render("post/show.html.twig", [
            "post" => $post
        ]);

    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @param Post $post
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Post $post)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($post);

        $em->flush();

        return $this->redirect($this->generateUrl('post.index'));
    }
}
