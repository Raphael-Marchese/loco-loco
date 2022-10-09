<?php

namespace App\Controller;

use App\Entity\Store;
use App\Entity\User;
use App\Form\StoreType;
use App\Repository\AddressRepository;
use App\Repository\StoreRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/store', name: 'store_')]
class StoreController extends AbstractController
{
    public function __construct(private StoreRepository $storeRepository, PaginatorInterface $paginator)
    {

    }

    #[Route('', name: 'index')]

    // show all producer stores
    public function storeIndex(): Response
    {
        $stores = $this->storeRepository->findBy([], ['name' => 'ASC']);

        return $this->render('store/index.html.twig', [
            'stores' => $stores,
        ]);
    }

    #[Route('/pro', name: 'indexpro')]

    // show all producer stores
    public function storeIndexPro(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->render('store/indexpro.html.twig', [
            'stores' => $user->getOwnedStores(),
        ]);
    }

/*    #[Route('/products', name: 'store-products', requirements:["id" => "\d+"])]
   public function getStoreProduct($id): Response
    {
        $store = $this->storeRepository->find($id);
        $product = $this->$store->getProducts();
        return $this->render('product/product_list.html.twig',[
            'products' => $product,
        ]);
    }*/

    #[Route('/locator', name: 'locator')]
    public function storeLocator(): Response
    {
        return $this->render('store/farms-locator.html.twig');
    }

    // Get single store information
    #[Route('/boutique/{slug}', name: 'single')]
    public function storeSingle(Store $store = null): Response
    {
        $storeAddress = $store->getAddresses()->getValues()[0];
        

        return $this->render('store/single.html.twig',
         ['storeInfo' => $store,
         'storeAddress' => $storeAddress
        ]);
    }

    #[Route('/single/about/{slug}', name: 'single-about')]
    public function storeSingleAbout(Store $store = null): Response
    {
        $storeAddress = $store->getAddresses()->getValues()[0];

        return $this->render('store/single-about.html.twig', [
            'storeInfo' => $store,
            'storeAddress' => $storeAddress

        ]);
    }


    #[Route('/create', name: 'create')]
    #[IsGranted('ROLE_PRODUCER')]
    //Add new store by producer
    public function form(Request $request, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        $newStore = new Store();

        $form = $this->createForm(StoreType::class, $newStore);
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
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {

                }

                $newStore->setPicture($newFilename);
            }
            $name= $form->get('name')->getData();
            $newStore->setSlug($slugger->slug($name));
            $newStore->setOwner($user);
            $newStore->getAddresses()->first()->setStores($newStore);
            $this->storeRepository->add($newStore,true);

            $this->addFlash('success', 'Votre boutique a été créée');
            return $this->redirectToRoute('main_index',[
                'id'=> $newStore->getId()
            ]);
        }

        $this->addFlash('error', 'erreur lors de la création de votre boutique');
        return $this->render('store/store_form.html.twig',[
            'form' => $form->createView()
        ]);
    }

}