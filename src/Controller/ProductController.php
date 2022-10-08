<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\EditProductType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\StoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/product', name: 'product_')]
class ProductController extends AbstractController
{
    public function __construct(private ProductRepository $productRepository, private PaginatorInterface $paginator)
    {

    }


    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('product/test_product.html.twig');
    }

    #[Route('/edit', name: 'edit')]
    public function editProducts(StoreRepository $storeRepository): Response
    {
        return $this->render('product/product_list.html.twig');
    }

    #[Route('/add', name: 'create')]
    //Add new store by producer
    public function form(Request $request, SluggerInterface $slugger): Response
    {

        $newProduct = new Product();

        $form = $this->createForm(EditProductType::class, $newProduct, [
            'user' => $this->getUser()
        ]);
        //  $newAddress = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $imageFile = $form->get('picture')->getData();
            // upload images
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('product_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {

                }

                $newProduct->setPicture($newFilename);
            }

            $form->get('stores')->getData()->addProduct($newProduct);

            $this->productRepository->add($newProduct,true);

            $this->addFlash('success', 'Votre boutique a été créée');
            return $this->redirectToRoute('main_index',[
                'id'=> $newProduct->getId()
            ]);
        }

        $this->addFlash('error', 'erreur lors de la création de votre boutique');
        return $this->render('product/product_form.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
