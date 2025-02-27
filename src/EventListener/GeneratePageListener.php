<?php

// src/EventListener/GeneratePageListener.php
namespace Marke\PostItBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\PageRegular;
use Contao\LayoutModel;
use Contao\ModuleModel;
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
            $postItId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
            $yCoordinate = isset($_GET['yCoordinate']) ? floatval($_GET['yCoordinate']) : '';
            $xCoordinate = isset($_GET['xCoordinate']) ? floatval($_GET['xCoordinate']) : '';
            $pArticle = isset($_GET['pArticle']) ? htmlspecialchars($_GET['pArticle']) : '';
            $title = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : '';
            $userinfo = isset($_GET['userinfo']) ? htmlspecialchars($_GET['userinfo']) : '';
            $bgColor = isset($_GET['bgColor']) ? htmlspecialchars($_GET['bgColor']) : '';
            $pageId = isset($_GET['pageId']) ? htmlspecialchars($_GET['pageId']) : '';
            $action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '';
            
            
            
            
            $tstamp = time();

            if ( $action === "save") {
            
                if ($yCoordinate && $xCoordinate && $pArticle) {

                    if ( $postItId && $postItId != "postit_new" ) {

                        $updatePostit = \Contao\Database::getInstance()->prepare("

                            UPDATE tl_postits 
                            SET title = ?, yCoordinate = ?, xCoordinate = ?, pArticle = ?, bgColor = ? WHERE id = ? 

                        ")->execute($title, $yCoordinate, $xCoordinate, $pArticle, $bgColor, $postItId);

                    } elseif ( $postItId === "postit_new" ) {

                        $createPostit = \Contao\Database::getInstance()->prepare("

                            INSERT INTO tl_postits (tstamp, title, yCoordinate, xCoordinate, pArticle, bgColor, userinfo,page)
                            VALUES (?, ?,?,?,?,?,?,?); 

                        ")->execute($tstamp, $title, $yCoordinate, $xCoordinate, $pArticle, $bgColor, $userinfo, $pageId);

                    }

                }
                
            } elseif ( $action === "delete" && $postItId ) { 
            
                $createPostit = \Contao\Database::getInstance()->prepare("

                    DELETE FROM tl_postits 
                    WHERE id = ?
        
                ")->execute($postItId);

            }
            

            
        }
        
    }
    
    
    private function loadPostits($pageId) {
        
        $loadPostIt = \Contao\Database::getInstance()->prepare("

            SELECT title,yCoordinate,xCoordinate,pArticle,id,bgColor
            FROM tl_postits 
            WHERE page = ?

        ")->execute($pageId);

        $postits_html = "";
        $toolbox_html = "";

        while($loadPostIt->next()) {

            $pid = $loadPostIt->pArticle;
            $id = $loadPostIt->id;
            $yCoordinate = $loadPostIt->yCoordinate;
            $xCoordinate = $loadPostIt->xCoordinate;
            $content = $loadPostIt->title;
            $bgColor = $loadPostIt->bgColor == '' ? 'wheat' : $loadPostIt->bgColor;

            $postits_html .= '
            <div id="postit_'.$id.'" class="post-it" data-yCoordinate="'.$yCoordinate.'" data-xCoordinate="'.$xCoordinate.'" data-pArticle="'.$pid.'" data-bgcolor="'.$bgColor.'">
                <div class="tape"></div>
                <div class="content">
                    <div class="title">
                        <textarea>'.$content.'</textarea>
                      
                    </div>
                    <div class="settings-bar">
                        <div class="saveIcon" onclick="savePostItData(`postit_'.$id.'`)"> 
                            <i class="fa-solid fa-floppy-disk"></i> 
                        </div>
                        <div class="pi-color-palette" data-bgColor="wheat" data-pPostIt="postit_'.$id.'"></div>
                        <div class="pi-color-palette" data-bgColor="red" data-pPostIt="postit_'.$id.'"></div>
                        <div class="saveIcon" onclick="deletePostItData(`postit_'.$id.'`)"> 
                            <i class="fa-solid fa-x"></i> 
                        </div>
                    </div>
                </div>
                
            </div>
            ';
            
            
        }
        
        
        $toolbox_html = '
            <div class="post-it-toolbox">
                <div style="display:none" id="post-it-visibility-toggler"><i class="fa-solid fa-eye"></i></div>
                <div id="post-it-new-element-icon"><i class="fa-solid fa-circle-plus"></i></div>
                
            </div>
            
        ';
        
        
        
        return $postits_html . $toolbox_html;
        
    }
    
    
    
    
    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        
        

        if ($this->tokenChecker->hasBackendUser()) { 
            
            $this->updateDatabase();
            
            $pageId = $pageModel->id;
            
            
            $GLOBALS['TL_BODY'][] = $this->loadPostits($pageId);
            $GLOBALS['TL_BODY'][] = "<script>const contaoPageId=" . $pageId ."</script>";
            
            $GLOBALS['TL_JAVASCRIPT'][] = "/bundles/markepostit/js/main.js";
            $GLOBALS['TL_CSS'][] = "/bundles/markepostit/css/main.css";
        
        }

        
    }
}
    