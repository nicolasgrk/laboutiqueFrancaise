<?php
namespace App\Classe;


use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Cart
{
    private RequestStack $requestStack;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;

    }

    public function initSession()
    {
        return $this->requestStack->getSession();
    }

    public function add($id): void
    {
        $cart = $this->initSession()->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->initSession()->set('cart', $cart);
    }

    public function get()
    {
        return $this->initSession()->get('cart');
    }

    public function remove(): mixed
    {
        return $this->initSession()->remove('cart');
    }
    public function delete($id): mixed
    {
        $cart = $this->initSession()->get('cart', []);
        unset($cart[$id]);
        return $this->initSession()->set('cart', $cart);

    }
    public function decrease($id){
        $cart = $this->initSession()->get('cart', []);
        if($cart[$id]>1){
            $cart[$id]--;
        }else{
            unset($cart[$id]);
        }
        return $this->initSession()->set('cart', $cart);
    }

    public function getFull(){
        $cartComplete =[];
        if($this->get()){
            foreach ($this->get() as $id => $quantity){
                $product_object = $this->entityManager->getRepository(Product::class)->findOneById($id);
                if(!$product_object){
                    $this->delete($id);
                    continue;
                }
                $cartComplete[] = [
                    'product'=>  $product_object,
                    'quantity' => $quantity
                ];
            }
        }
        return $cartComplete;
    }

}