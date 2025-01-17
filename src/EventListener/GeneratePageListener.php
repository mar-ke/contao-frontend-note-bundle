<?php

// src/EventListener/GeneratePageListener.php
namespace Mar-ke\ContaoFrontendPostitBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\PageRegular;
use Contao\LayoutModel;
use Contao\PageModel;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\BackendUser;
use Contao\FrontendUser;

#[AsHook('generatePage')]
class GeneratePageListener
{
    
    public function __construct(private readonly TokenChecker $tokenChecker)
    {
    }
    
    
    
    
    
    private function updateDatabase() {
         
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            // Eingehende Daten aus den GET-Parametern lesen
            $postItId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null;
            $yCoordinate = isset($_GET['yCoordinate']) ? floatval($_GET['yCoordinate']) : null;
            $xCoordinate = isset($_GET['xCoordinate']) ? floatval($_GET['xCoordinate']) : null;
            $pArticle = isset($_GET['pArticle']) ? htmlspecialchars($_GET['pArticle']) : null;

            if ( $postItId && $yCoordinate && $xCoordinate && $pArticle ) {
            
                $updatePostIt = \Contao\Database::getInstance()->prepare("

                    UPDATE tl_vendor 
                    SET postal = ?, city = ?, street = ? WHERE id = ? 

                ")->execute($yCoordinate, $xCoordinate, $pArticle, $postItId);
                
            }
        }
        
    }
    
    
    private function loadPostits() {
        
        $loadPostIt = \Contao\Database::getInstance()->prepare("

            SELECT postal,street,city,id,name
            FROM tl_vendor 

        ")->execute();

        $postits_arr = "";

        while($loadPostIt->next()) {

            $pid = $loadPostIt->street;
            $id = $loadPostIt->id;
            $yCoordinate = $loadPostIt->postal;
            $xCoordinate = $loadPostIt->city;
            $content = $loadPostIt->name;

            $postits_arr .= '<div id="postit_'.$id.'" class="post-it" data-yCoordinate="'.$yCoordinate.'" data-xCoordinate="'.$xCoordinate.'" data-pArticle="'.$pid.'"><div onclick="savePostItData(`postit_'.$id.'`)"> speichern </div>'.$content.'</div>';

            
            
        }
        
        return $postits_arr;
        
    }
    
    
    
    
    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        
        

        if ($this->tokenChecker->hasBackendUser()) { 
            
            $this->updateDatabase();
            
            
            $GLOBALS['TL_BODY'][] = $this->loadPostits();
        
        }

        
    }
}
    