(()=>{var e,t,c,a=window.location.origin+"/assets/json/",i="";document.querySelector("#mailLoader");function l(e){document.querySelector('#mail-filter-navlist button[data-bs-target="#pills-primary"]').click(),document.querySelector("#mail-list").innerHTML="",e.forEach((function(e,t){var c=e.readed?"":"unread",a=e.starred?"active":"",i=e.counted?"("+e.counted+")":"";document.querySelector("#mail-list").innerHTML+='<li class="'+c+'">        <div class="col-mail col-mail-1">            <div class="form-check checkbox-wrapper-mail fs-14">                <input class="form-check-input" type="checkbox" value="'+e.id+'" id="checkbox-'+e.id+'">                <label class="form-check-label" for="checkbox-'+e.id+'"></label>            </div>            <input type="hidden" value='+e.userImg+' class="mail-userimg" />            <button type="button" class="btn avatar-xs p-0 favourite-btn fs-15 '+a+'">            <i class="ri-star-fill"></i>            </button>            <a href="javascript: void(0);" class="title"><span class="title-name">'+e.name+"</span> "+i+'</a>        </div>        <div class="col-mail col-mail-2">            <a href="javascript: void(0);" class="subject"><span class="subject-title">'+e.title+'</span> – <span class="teaser">'+e.description+'</span>            </a>            <div class="date">'+e.date+"</div>        </div>    </li>",n(),o(),u(),s()}))}function n(){document.querySelectorAll(".favourite-btn").forEach((function(e){e.addEventListener("click",(function(){e.classList.contains("active")?e.classList.remove("active"):e.classList.add("active")}))}))}function o(){var e=document.getElementsByTagName("body")[0];document.querySelectorAll(".message-list a").forEach((function(t){t.addEventListener("click",(function(t){e.classList.add("email-detail-show"),document.querySelectorAll(".message-list li.unread").forEach((function(e){e.classList.contains("unread")&&t.target.closest("li").classList.remove("unread")}))}))})),document.querySelectorAll(".close-btn-email").forEach((function(t){t.addEventListener("click",(function(){e.classList.remove("email-detail-show")}))}));var t=!1,c=document.getElementsByClassName("email-menu-sidebar");document.querySelectorAll(".email-menu-btn").forEach((function(e){e.addEventListener("click",(function(){c.forEach((function(e){e.classList.add("menubar-show"),t=!0}))}))})),window.addEventListener("click",(function(e){document.querySelector(".email-menu-sidebar").classList.contains("menubar-show")&&(t||document.querySelector(".email-menu-sidebar").classList.remove("menubar-show"),t=!1)})),n()}function s(){function e(){var e=document.querySelectorAll(".checkbox-wrapper-mail input"),c=document.querySelectorAll(".checkbox-wrapper-mail input:checked").length;e.forEach((function(e){e.checked=!0,e.parentNode.parentNode.parentNode.classList.add("active")})),document.getElementById("email-topbar-actions").style.display=c>0?"none":"block",c>0?e.forEach((function(e){e.checked=!1,e.parentNode.parentNode.parentNode.classList.remove("active")})):e.forEach((function(e){e.checked=!0,e.parentNode.parentNode.parentNode.classList.add("active")})),this.onclick=t,d()}function t(){var t=document.querySelectorAll(".checkbox-wrapper-mail input"),c=document.querySelectorAll(".checkbox-wrapper-mail input:checked").length;t.forEach((function(e){e.checked=!1,e.parentNode.parentNode.parentNode.classList.remove("active")})),document.getElementById("email-topbar-actions").style.display=c>0?"none":"block",c>0?t.forEach((function(e){e.checked=!1,e.parentNode.parentNode.parentNode.classList.remove("active")})):t.forEach((function(e){e.checked=!0,e.parentNode.parentNode.parentNode.classList.add("active")})),this.onclick=e}document.querySelectorAll(".checkbox-wrapper-mail input").forEach((function(e){e.addEventListener("click",(function(e){1==e.target.checked?e.target.closest("li").classList.add("active"):e.target.closest("li").classList.remove("active")}))})),document.querySelectorAll(".checkbox-wrapper-mail input").forEach((function(e){e.addEventListener("click",(function(e){var t=document.querySelectorAll(".checkbox-wrapper-mail input"),c=document.getElementById("checkall"),a=document.querySelectorAll(".checkbox-wrapper-mail input:checked").length;c.checked=a>0,c.indeterminate=a>0&&a<t.length,e.target.closest("li").classList.contains("active"),document.getElementById("email-topbar-actions").style.display=a>0?"block":"none"}))})),document.getElementById("checkall").onclick=e}e="mail-list.init.json",t=function(e,t){null!==e?console.log("Something went wrong: "+e):(i=t[0].primary,socialmaillist=t[0].social,promotionsmaillist=t[0].promotions,l(i),socialmaillist.forEach((function(e,t){var c=e.readed?"":"unread",a=e.starred?"active":"",i=e.counted?"("+e.counted+")":"";document.getElementById("social-mail-list").innerHTML+='<li class="'+c+'">                <div class="col-mail col-mail-1">                    <div class="form-check checkbox-wrapper-mail fs-14">                        <input class="form-check-input" type="checkbox" value="'+e.id+'" id="checkbox-'+e.id+'">                        <label class="form-check-label" for="checkbox-'+e.id+'"></label>                    </div>                    <input type="hidden" value='+e.userImg+' class="mail-userimg" />                    <button type="button" class="btn avatar-xs p-0 favourite-btn fs-15 '+a+'">                    <i class="ri-star-fill"></i>                    </button>                    <a href="javascript: void(0);" class="title"><span class="title-name">'+e.name+"</span> "+i+'</a>                </div>                <div class="col-mail col-mail-2">                    <a href="javascript: void(0);" class="subject"><span class="subject-title">'+e.title+'</span> – <span class="teaser">'+e.description+'</span>                    </a>                    <div class="date">'+e.date+"</div>                </div>            </li>",o(),u(),s()})),function(e){e.forEach((function(e,t){var c=e.readed?"":"unread",a=e.starred?"active":"",i=e.counted?"("+e.counted+")":"";document.getElementById("promotions-mail-list").innerHTML+='<li class="'+c+'">                <div class="col-mail col-mail-1">                    <div class="form-check checkbox-wrapper-mail fs-14">                        <input class="form-check-input" type="checkbox" value="'+e.id+'" id="checkbox-'+e.id+'">                        <label class="form-check-label" for="checkbox-'+e.id+'"></label>                    </div>                    <input type="hidden" value='+e.userImg+' class="mail-userimg" />                    <button type="button" class="btn avatar-xs p-0 favourite-btn fs-15 '+a+'">                    <i class="ri-star-fill"></i>                    </button>                    <a href="javascript: void(0);" class="title"><span class="title-name">'+e.name+"</span> "+i+'</a>                </div>                <div class="col-mail col-mail-2">                    <a href="javascript: void(0);" class="subject"><span class="subject-title">'+e.title+'</span> – <span class="teaser">'+e.description+'</span>                    </a>                    <div class="date">'+e.date+"</div>                </div>            </li>",o(),u(),s()}))}(promotionsmaillist))},(c=new XMLHttpRequest).open("GET",a+e,!0),c.responseType="json",c.onload=function(){var e=c.status;200===e?(document.getElementById("mailLoader").innerHTML="",t(null,c.response)):t(e,c.response)},c.send(),document.querySelectorAll(".mail-list a").forEach((function(e){e.addEventListener("click",(function(){var t=document.querySelector(".mail-list a.active");if(t&&t.classList.remove("active"),e.classList.add("active"),e.querySelector(".mail-list-link").hasAttribute("data-type"))var c=e.querySelector(".mail-list-link").innerHTML,a=i.filter((function(e){return e.labeltype===c}));else{c=e.querySelector(".mail-list-link").innerHTML;if(document.getElementById("mail-list").innerHTML="","All"!=c)a=i.filter((function(e){return e.tabtype===c}));else a=i;document.getElementById("mail-filter-navlist").style.display="All"!=c&&"Inbox"!=c?"none":"block"}l(a),n()}))})),n(),ClassicEditor.create(document.querySelector("#email-editor")).then((function(e){e.ui.view.editable.element.style.height="200px"})).catch((function(e){console.error(e)}));function r(e){setTimeout((function(){var t=document.getElementById(e).querySelector("#chat-conversation .simplebar-content-wrapper")?document.getElementById(e).querySelector("#chat-conversation .simplebar-content-wrapper"):"",c=document.getElementsByClassName("chat-conversation-list")[0]?document.getElementById(e).getElementsByClassName("chat-conversation-list")[0].scrollHeight-window.innerHeight+750:0;c&&t.scrollTo({top:c,behavior:"smooth"})}),100)}function d(){document.getElementById("removeItemModal").addEventListener("show.bs.modal",(function(e){document.getElementById("delete-record").addEventListener("click",(function(){document.querySelectorAll(".message-list li").forEach((function(e){var t,c="";if(e.classList.contains("active")){var a=e.querySelector(".form-check-input").value;c=(t=a,i.filter((function(e){return e.id!=t})));i=c,e.remove()}})),document.getElementById("btn-close").click(),document.getElementById("email-topbar-actions")&&(document.getElementById("email-topbar-actions").style.display="none"),checkall.indeterminate=!1,checkall.checked=!1}))}))}r("users-chat"),d(),document.getElementById("mark-all-read").addEventListener("click",(function(e){if(0===document.querySelectorAll(".message-list li.unread").length){document.getElementById("unreadConversations").style.display="block",setTimeout((function(){document.getElementById("unreadConversations").style.display="none"}),1e3)}document.querySelectorAll(".message-list li.unread").forEach((function(e){e.classList.contains("unread")&&e.classList.remove("unread")}))}));function u(){document.querySelectorAll(".message-list li").forEach((function(e){e.addEventListener("click",(function(){var t=e.querySelector(".subject-title").innerHTML;document.querySelector(".email-subject-title").innerHTML=t;var c=e.querySelector(".title-name").innerHTML;document.querySelectorAll(".accordion-item.left").forEach((function(t){t.querySelector(".email-user-name").innerHTML=c;var a=e.querySelector(".mail-userimg").value;t.querySelector("img").setAttribute("src",a)}));var a=document.querySelector(".user-name-text").innerHTML,i=document.querySelector(".header-profile-user").getAttribute("src");document.querySelectorAll(".accordion-item.right").forEach((function(e){e.querySelector(".email-user-name-right").innerHTML=a,e.querySelector("img").setAttribute("src",i)}))}))}))}document.querySelectorAll(".email-chat-list a").forEach((function(e){e.addEventListener("click",(function(t){document.getElementById("emailchat-detailElem").style.display="block",!0;var c=document.querySelector(".email-chat-list a.active");c&&c.classList.remove("active"),this.classList.add("active");r("users-chat");var a=e.querySelector(".chatlist-user-name").innerHTML,i=e.querySelector(".chatlist-user-image img").getAttribute("src");document.querySelector(".email-chat-detail .profile-username").innerHTML=a,document.getElementById("users-conversation").querySelectorAll(".left .chat-avatar").forEach((function(e){i?e.querySelector("img").setAttribute("src",i):e.querySelector("img").setAttribute("src","assets/images/users/user-dummy-img.jpg")}))}))})),document.getElementById("emailchat-btn-close").addEventListener("click",(function(){document.getElementById("emailchat-detailElem").style.display="none"}))})();