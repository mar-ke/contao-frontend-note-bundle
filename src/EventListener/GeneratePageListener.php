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
                            <span class="fen-icon --floppy"></span>
                        </div>
                        <div>
                            <div class="fen-color-palette" data-bgColor="wheat" data-pPostIt="postit_'.$id.'"></div>
                            <div class="fen-color-palette" data-bgColor="crimson" data-pPostIt="postit_'.$id.'"></div>
                            <div class="fen-color-palette" data-bgColor="darkcyan" data-pPostIt="postit_'.$id.'"></div>
                            
                            
                        </div>
                        <div onclick="deletePostItData(`postit_'.$id.'`)" class="deleteIcon"> 
                            <span class="fen-icon --xmark"></span>
                        </div>
                    </div>
                </div>
                
            </div>
            '; 
            
        }

        $toolbox_html = '
            <div class="frontend-note-toolbox">
                <div style="display:none" id="frontend-note-visibility-toggler"><i class="fa-solid fa-eye"></i></div>
                <div id="frontend-note-new-element-icon"><span class="fen-icon --circle-plus"></span>Neuer Frontendnote</div>
                
            </div>
            
        ';

        return $postits_html . $toolbox_html;
        
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {

        if ($this->tokenChecker->hasBackendUser()) { 

            $pageId = $pageModel->id;

            $GLOBALS['TL_BODY'][] = $this->loadPostits($pageId);
            $GLOBALS['TL_BODY'][] = "<script>const contaoPageId=" . $pageId ."</script>";
            
            $GLOBALS['TL_JAVASCRIPT'][] = "/bundles/markefrontendnote/js/main.js";
            $GLOBALS['TL_CSS'][] = "/bundles/markefrontendnote/css/main.css";
        
        }

        
    }
}
    