<?php

// src/EventListener/GeneratePageListener.php
namespace Marke\FrontendNoteBundle\EventListener;

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

    
    private function checkResponse() {

            header('Content-Type: application/json');
            
            // für Insert nehme ich 
            // $createPostit->insertId

            // $updatePostit->affectedRows
            
            $GLOBALS['TL_HEAD'][] = 'Content-Type: application/json';
            
            if ( 3 ) {
                echo "true";
            } else {
                echo "false";
            }
            exit();
            
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

                            UPDATE tl_frontendnotes 
                            SET title = ?, yCoordinate = ?, xCoordinate = ?, pArticle = ?, bgColor = ? WHERE id = ? 

                        ")->execute($title, $yCoordinate, $xCoordinate, $pArticle, $bgColor, $postItId);

                        $this->checkResponse();
                        
                    } elseif ( $postItId === "postit_new" ) {

                        $createPostit = \Contao\Database::getInstance()->prepare("

                            INSERT INTO tl_frontendnotes (tstamp, title, yCoordinate, xCoordinate, pArticle, bgColor, userinfo,page,user)
                            VALUES (?,?,?,?,?,?,?,?,?); 

                        ")->execute($tstamp, $title, $yCoordinate, $xCoordinate, $pArticle, $bgColor, $userinfo, $pageId, $this->tokenChecker->getBackendUsername());
        
                    }

                }
                
            } elseif ( $action === "delete" && $postItId ) { 
            
                $createPostit = \Contao\Database::getInstance()->prepare("

                    DELETE FROM tl_frontendnotes 
                    WHERE id = ?
        
                ")->execute($postItId);

            }
            

            
        }
        
    }
    
    
    private function loadPostits($pageId) {
        
        $loadPostIt = \Contao\Database::getInstance()->prepare("

            SELECT title,yCoordinate,xCoordinate,pArticle,id,bgColor,user
            FROM tl_frontendnotes 
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
            $author = $loadPostIt->user;

            $postits_html .= '
            <div id="postit_'.$id.'" class="frontend-note saved" data-yCoordinate="'.$yCoordinate.'" data-xCoordinate="'.$xCoordinate.'" data-pArticle="'.$pid.'" data-bgcolor="'.$bgColor.'">
                <div class="tape"></div>
                <div class="author">von '.$author.':</div>
                <div class="content">
                    <div class="title">
                        <textarea onInput="saveable(`postit_'.$id.'`)">'.$content.'</textarea>
                      
                    </div>
                    <div class="settings-bar">
                        <div class="saveIcon" onclick="savePostItData(`postit_'.$id.'`)"> 
                            <i class="fa-solid fa-floppy-disk"></i> 
                        </div>
                        <div>
                            <div class="fen-color-palette" data-bgColor="wheat" data-pPostIt="postit_'.$id.'"></div>
                            <div class="fen-color-palette" data-bgColor="crimson" data-pPostIt="postit_'.$id.'"></div>
                            <div class="fen-color-palette" data-bgColor="darkcyan" data-pPostIt="postit_'.$id.'"></div>
                            
                            
                        </div>
                        <div onclick="deletePostItData(`postit_'.$id.'`)" class="deleteIcon"> 
                            <i class="fa-solid fa-xmark"></i>
                        </div>
                    </div>
                </div>
                
            </div>
            ';
            
            
        }
        
        
        $toolbox_html = '
            <div class="frontend-note-toolbox">
                <div style="display:none" id="frontend-note-visibility-toggler"><i class="fa-solid fa-eye"></i></div>
                <div id="frontend-note-new-element-icon"><i class="fa-solid fa-circle-plus"></i></div>
                
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
            
            $GLOBALS['TL_JAVASCRIPT'][] = "/bundles/markefrontendnote/js/main.js";
            $GLOBALS['TL_CSS'][] = "/bundles/markefrontendnote/css/main.css";
        
        }

        
    }
}
    