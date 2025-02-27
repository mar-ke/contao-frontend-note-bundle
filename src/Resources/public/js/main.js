let mouseOverTape = false;

function setCookie(cname, cvalue) {
  const d = new Date();
  d.setTime(d.getTime() + (28*24*60*60*1000));
  let expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/;sameSite=None;secure=true";
}	

function getCookie(cname) {
    
    let cookieValue = "";
        
    const allCookies = document.cookie.split(";");
    
    if ( Array.isArray(allCookies) ) {
        
        allCookies.forEach( (element) => {
                    
            if ( element.includes(cname) ) {

                cookieValue = element.split("=")[1];

            }
        });
    }
    
    return cookieValue;
}

function togglePostItVisibility() {
        
    const postItVisibilityToggler = document.getElementById("post-it-visibility-toggler");
    
    
    if ( postItVisibilityToggler ) {
    
        const eyeIcon = postItVisibilityToggler.getElementsByTagName('i')[0];
        
        postItVisibilityToggler.addEventListener("click", function (e) {

            if ( getCookie('pi-visibility') == "1" ) {
                setCookie('pi-visibility', 0)
                
                eyeIcon.className = "fa-solid fa-eye-slash"
                
            } else {
                setCookie('pi-visibility', 1)
                
                
                eyeIcon.className = "fa-solid fa-eye"
            }

            
            checkVisibility()

        });

    }
    
}


function iniTogglePostItVisibilityIcon() {
        
    const postItVisibilityToggler = document.getElementById("post-it-visibility-toggler");
    
    
    if ( postItVisibilityToggler ) {
    
        const eyeIcon = postItVisibilityToggler.getElementsByTagName('i')[0];

        if ( getCookie('pi-visibility') == "1" ) {
            eyeIcon.className = "fa-solid fa-eye"
        } else {
            eyeIcon.className = "fa-solid fa-eye-slash"
        }


    }
    
}

function iniColorPalette() {
    
    const elements = document.querySelectorAll('.pi-color-palette');

    elements.forEach(element => {
        element.onclick = function() {
            changePostItBgC(this); 
        };
    });
    
}

function iniCreateIcon() {
    
    const createIcon = document.getElementById('post-it-new-element-icon');

    createIcon.onclick = function() {
        createNewPostIt(newPostItHTML); 
    };

}
	
// Funktion, die alle vorhandenen .post-it-Elemente im DOM positioniert
function positionPostIts() {
    const postIts = document.querySelectorAll('.post-it');

    postIts.forEach(postIt => {
        const articleId = postIt.getAttribute('data-pArticle');
        const yCoordinate = parseFloat(postIt.getAttribute('data-yCoordinate'));
        const xCoordinate = parseFloat(postIt.getAttribute('data-xCoordinate'));

        const article = document.getElementById(articleId);

        if (!article) {
            console.error(`Element mit der ID "${articleId}" wurde nicht gefunden.`);
            return;
        }

        const top =  article.offsetTop;
        const left = article.offsetLeft; 
        const height = article.offsetHeight;
        const width = article.offsetWidth;
        
        // Position für das Element berechnen
        
        let newTop = top + height * (yCoordinate / 100);
        let newLeft = left + width * (xCoordinate / 100);
    
        if (  ( newLeft + postIt.offsetWidth ) > window.innerWidth ) {
            newLeft = window.innerWidth - postIt.offsetWidth * 1.2
        }
        
        // Element positionieren
        postIt.style.top = `${newTop}px`;
        postIt.style.left = `${newLeft}px`;
        
        enableDrag(postIt, article);
        
    });
}



// Funktion, um ein Element beweglich zu machen
function enableDrag(postIt, article) {
    let isDragging = false;
    let startX, startY, startLeft, startTop;
    
    let tape = postIt.getElementsByClassName('tape')[0];

    postIt.addEventListener('mousedown', (event) => {
        
        if (event.button === 0 && mouseOverTape) { 
            isDragging = true;
            startX = event.clientX;
            startY = event.clientY;
            
            const rect = postIt.getBoundingClientRect();
            startLeft = rect.left + window.scrollX; // Absolut positioniert
            startTop = rect.top + window.scrollY;  // Absolut positioniert
            
            if (!postIt.classList.contains("isDragged")) {
                postIt.classList.add('isDragged')
            }
            
            tape.style.cursor ="grabbing";
            
            // event.preventDefault(); // Verhindert unerwünschtes Markieren
        
        }
    });

    document.addEventListener('mousemove', (event) => {
        if (isDragging) {
            const deltaX = event.clientX - startX;
            const deltaY = event.clientY - startY;

            const newLeft = startLeft + deltaX;
            const newTop = startTop + deltaY;

            postIt.style.left = `${newLeft}px`;
            postIt.style.top = `${newTop}px`;
        }
    });

    document.addEventListener('mouseup', (event) => {
        if (isDragging) {
            isDragging = false;
            
            tape.style.cursor ="grab";
            
            postIt.classList.toggle('isDragged')

            // Überprüfen, über welchem Artikel sich das Post-it befindet
            const allArticles = document.querySelectorAll('.mod_article.block');
            let targetArticle = null;


            // Zunächst nach dem Artikel in der Mitte suchen
            for (let article of allArticles) {
                const articleRect = article.getBoundingClientRect();
                if (
                    event.clientX >= articleRect.left &&
                    event.clientX <= articleRect.right &&
                    event.clientY >= articleRect.top &&
                    event.clientY <= articleRect.bottom
                ) {
                    targetArticle = article;
                    break;
                }
            }

            // Falls kein Artikel gefunden wurde, suche den nächsthöheren
            if (!targetArticle) {
                for (let i = allArticles.length - 1; i >= 0; i--) {
                    const article = allArticles[i];
                    const articleRect = article.getBoundingClientRect();
                    if (articleRect.bottom <= event.clientY) {
                        targetArticle = article;
                        break;
                    }
                }
            }

            // Falls immer noch kein Artikel gefunden wurde, nehme den ersten
            if (!targetArticle) {
                targetArticle = allArticles[0] || null;
            }


            if (targetArticle) {
                const articleRect = targetArticle.getBoundingClientRect();
                const postItRect = postIt.getBoundingClientRect();

                const newY = ((postItRect.top - articleRect.top) / articleRect.height) * 100;
                const newX = ((postItRect.left - articleRect.left) / articleRect.width) * 100;

                // Aktualisieren der Attributwerte
                postIt.setAttribute('data-yCoordinate', newY.toFixed(2));
                postIt.setAttribute('data-xCoordinate', newX.toFixed(2));
                postIt.setAttribute('data-pArticle', targetArticle.id);
            }
        }
    });
}

function savePostItData(postItId) {
    
    const postIt = document.getElementById(postItId);

    let id;
    
    // Die Parameter aus den Attributen des Post-its extrahieren
    if ( postIt.id == "postit_new" ) {
        id = encodeURIComponent("postit_new"); 

    } else {
        id = encodeURIComponent(postIt.id.split("_")[1]); 

    }

    const yCoordinate = encodeURIComponent(postIt.getAttribute('data-yCoordinate'));
    const xCoordinate = encodeURIComponent(postIt.getAttribute('data-xCoordinate'));
    const pArticle = encodeURIComponent(postIt.getAttribute('data-pArticle'));
    const bgColor = encodeURIComponent(postIt.getAttribute('data-bgColor'));
    const title = encodeURIComponent(postIt.getElementsByTagName('textarea')[0].value);
    const userinfo = encodeURIComponent(postIt.getAttribute('data-userinfo'));
    const pageId = encodeURIComponent(postIt.getAttribute('data-pageid'));

    // URL mit den Parametern erstellen
    const params = `id=${id}&yCoordinate=${yCoordinate}&xCoordinate=${xCoordinate}&pArticle=${pArticle}&title=${title}&bgColor=${bgColor}&userinfo=${userinfo}&pageId=${pageId}&action=save`;
    const url = `${window.location.href}?${params}`;

    console.log(url);

    // GET-Anfrage senden
    fetch(url, {
        method: 'GET',
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Fehler beim Speichern: ${response.statusText}`);
        }
        return response.text(); // Einfacher Text als Antwort
    })
    .then(responseData => {
        console.log('Post-it-Daten erfolgreich gespeichert:', responseData);
        
        if (id == "postit_new" ) {

            window.location.reload()

        }
        
    })
    .catch(error => {
        console.error('Fehler beim Speichern:', error);
    });
    
} 


function deletePostItData(postItId) {
    
    const postIt = document.getElementById(postItId);
    const id = encodeURIComponent(postIt.id.split("_")[1]); 

    // URL mit den Parametern erstellen
    const params = `id=${id}&action=delete`;
    const url = `${window.location.href}?${params}`;

    console.log(url);

    // GET-Anfrage senden
    fetch(url, {
        method: 'GET',
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Fehler beim löschen: ${response.statusText}`);
        }
        return response.text(); // Einfacher Text als Antwort
    })
    .then(responseData => {
        console.log('Post-it-Daten erfolgreich gespeichert:', responseData);
        
        window.location.reload()

        
    })
    .catch(error => {
        console.error('Fehler beim Speichern:', error);
    });
    
}



function checkVisibility() {
    
    const bodyTag = document.getElementsByTagName('body')[0];
    
    if ( getCookie('pi-visibility') == "1" && bodyTag.classList.contains('hide-post-its') ) {
        
        bodyTag.classList.remove('hide-post-its')
        
    } 
    
    if ( getCookie('pi-visibility') == "0" && !bodyTag.classList.contains('hide-post-its') ) {
        
        bodyTag.classList.add('hide-post-its')
        
    } 
    
    
}

function changePostItBgC(element) {
    
    const ppostitId = element.getAttribute('data-ppostit');
    const bgColor = element.getAttribute('data-bgColor');
    const ppostit = document.getElementById(ppostitId);
    
    
    ppostit.setAttribute("data-bgColor",bgColor)
    
    
}





// Eventlistener hinzufügen, um alle .post-it-Elemente neu zu positionieren, wenn das Fenster neu skaliert wird
window.addEventListener('resize', positionPostIts);

const newPostItHTML = `
<div id="postit_new" class="post-it" data-yCoordinate="" data-xCoordinate="" data-pArticle="">
    <div class="tape"></div>
    <div class="content">
        <div class="title">
            <textarea>Neues Post-It - hier Text verändern</textarea>
        </div>
        <div class="settings-bar">
            <div class="saveIcon" onclick="savePostItData('postit_new')">
                <i class="fa-solid fa-floppy-disk"></i>
            </div>
        </div>
    </div>
</div>`;


function createNewPostIt(newPostItHTML) {
    
    const firstArticle = document.querySelector('#main .mod_article');
    
    const windowYCenter = window.innerHeight/2 + window.scrollY;
    const windowXCenter = window.innerWidth/2 + window.scrollX;
    
    const allArticles = document.querySelectorAll('.mod_article.block');
    let targetArticle = null;

    // Zunächst nach dem Artikel in der Mitte suchen
    for (let article of allArticles) {
        const articleRect = article.getBoundingClientRect();
        if (
            windowXCenter >= articleRect.left + window.scrollX &&
            windowXCenter <= articleRect.right + window.scrollX &&
            windowYCenter >= articleRect.top + window.scrollY &&
            windowYCenter <= articleRect.bottom + window.scrollY
        ) {
            targetArticle = article;
            break;
        }
    }

    // Falls kein Artikel gefunden wurde, suche den nächsthöheren
    if (!targetArticle) {
        for (let i = allArticles.length - 1; i >= 0; i--) {
            const article = allArticles[i];
            const articleRect = article.getBoundingClientRect();
            if (articleRect.bottom <= windowYCenter) {
                targetArticle = article;
                break;
            }
        }
    }

    // Falls immer noch kein Artikel gefunden wurde, nehme den ersten
    if (!targetArticle) {
        targetArticle = allArticles[0] || null;
    }
    
    const userInfoObj = {
        
        "userAgent" : navigator.userAgent,
        "platform" :  navigator.platform,
        "availableWidth" :  window.screen.availWidth,
        "availableHeight" :  window.screen.availHeight,
        "pixelRatio" :  window.devicePixelRatio

    }
    
    const userInfo = JSON.stringify(userInfoObj);
    
    // Temporäres Element erstellen
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = newPostItHTML; // HTML-Code in das temporäre Element laden

    // Das erste Kind (das neue Post-It) extrahieren
    const newElement = tempDiv.firstElementChild;
    
    newElement.style.top = `${windowYCenter}px`;
    newElement.style.left = `${windowXCenter}px`;

    newElement.setAttribute('data-particle',targetArticle.id)
    newElement.setAttribute('data-xcoordinate',"50")
    newElement.setAttribute('data-ycoordinate',"50")
    newElement.setAttribute('data-userinfo',userInfo)
    newElement.setAttribute('data-pageId',contaoPageId)

    
    
    enableDrag(newElement, targetArticle)
    
    // Das neue Element ans Ende des Body anhängen
    document.body.append(newElement);
    
    
}


function setTapeEventListener() {
    document.querySelectorAll('.tape').forEach(tape => {
        tape.addEventListener('pointerenter', () => {
            mouseOverTape = true;
        });

        tape.addEventListener('pointerleave', () => {
            mouseOverTape = false;
        });
    });
}


// initialize

window.addEventListener('load', function() {
    positionPostIts();
    // togglePostItVisibility();
    // iniTogglePostItVisibilityIcon();
    // checkVisibility();
    iniColorPalette();
    iniCreateIcon();
    setTapeEventListener();
});
