<?php

namespace Marke\FrontendNoteBundle\Controller;

use Doctrine\DBAL\Connection;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request; 
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager; 
use Symfony\Component\Security\Csrf\CsrfToken; 
use Symfony\Component\HttpFoundation\Response;
use Contao\System;
use Contao\BackendUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;


class AjaxDataController extends AbstractController
{
    
    // private $db;
    // private $token;
    
    private Connection $db;
    private ContaoCsrfTokenManager $tokenManager;
    private string $csrfTokenName;
    private TokenChecker $tokenChecker;
    
    public function __construct(
        Connection $db,
        ContaoCsrfTokenManager $tokenManager,
        string $csrfTokenName,
        TokenChecker $tokenChecker
    ) {
        
        $this->db = $db;
        $this->tokenManager = $tokenManager;
        $this->csrfTokenName = $csrfTokenName;
        $this->tokenChecker = $tokenChecker;
        
        $this->backendUserName = $tokenChecker->getBackendUsername();

        if ( $this->backendUserName ) {
            
            $user = $this->db->fetchAssociative(
                'SELECT id FROM tl_user WHERE username = ?',
                [$this->backendUserName]
            );
            
            $this->user_id = $user['id'];
            
        }
        
    }
    
    #[Route('/_ajax/frontendnote', name: 'frontendnote')]
    public function frontendnote(Request $request): JsonResponse
    {

        if ( isset( $this->user_id ) ) {

            // Eingehende Daten aus den GET-Parametern lesen
            $postItId = Input::get('id') ?: '';
            $yCoordinate = Input::get('yCoordinate']) ?: '';
            $xCoordinate = Input::get('xCoordinate']) ?: '';
            $pArticle = Input::get('pArticle']) ?: '';
            $title = Input::get('title']) ?: '';
            $userinfo = Input::get('userinfo']) ?: '';
            $bgColor = Input::get('bgColor']) ?: '';
            $pageId = Input::get('pageId']) ?: '';
            $action = Input::get('action']) ?: '';
            
            $tstamp = time();
                
            if ( $action === "save") {
            
                if ($yCoordinate && $xCoordinate && $pArticle) {
            
                    if ( $postItId && $postItId != "postit_new" ) {
            
                        $updatePostit = $this->db->fetchAssociative(
                            'UPDATE tl_frontendnotes 
                            SET title = ?, yCoordinate = ?, xCoordinate = ?, pArticle = ?, bgColor = ? WHERE id = ? ',
                            [$title, $yCoordinate, $xCoordinate, $pArticle, $bgColor, $postItId]
                        );  
            
                        return new JsonResponse([
                                'success' => true,
                                'action' => 'update'
                        ]);
                        
                        // $this->checkResponse();
                        
                    } elseif ( $postItId === "postit_new" ) {
            
                        $createPostit = $this->db->fetchAssociative("
            
                            INSERT INTO tl_frontendnotes (tstamp, title, yCoordinate, xCoordinate, pArticle, bgColor, userinfo,page,user)
                            VALUES (?,?,?,?,?,?,?,?,?)",
                            [$tstamp, $title, $yCoordinate, $xCoordinate, $pArticle, $bgColor, $userinfo, $pageId, $this->tokenChecker->getBackendUsername()]
                        );
                        
                        return new JsonResponse([
                                'success' => true,
                                'action' => 'create'
                        ]);
            
                    }
            
                }
                
            } elseif ( $action === "delete" && $postItId ) { 
            
                $createPostit = $this->db->fetchAssociative("
            
                    DELETE FROM tl_frontendnotes 
                    WHERE id = ?",
                    [$postItId]
            
                );
                
                return new JsonResponse([
                        'success' => true,
                        'action' => 'delete'
                ]);
            
            }

        } 
        
        
        // fallback    
        return new JsonResponse([
            'success' => false,
        ]);
            

 

 
 
    }
     
}