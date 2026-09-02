/*
Demo PHP-class VigenereCipher
Version: 1.0, 2026-07-22
Author: Vladimir Kheifets (vladimir.kheifets.@online.de)
Copyright (c) 2026 Vladimir Kheifets All Rights Reserved
*/

const show = document.querySelectorAll("input[name='show']")[0];
const chAll = document.querySelectorAll("input[name='all']")[0];
const ch = document.querySelectorAll("input[name='extend[]']");
const ra = document.querySelectorAll("input[name='randomKey']");
const ke = document.querySelectorAll("input[name='key']")[0];
const inputTxt = document.querySelectorAll("input[name='inputTxt']");
const txt = document.querySelectorAll("textarea")[0];
const txtL = document.querySelectorAll("span")[0];
const send = document.querySelectorAll("input[name='send']")[0];
const maxTxtLen = 150;
//-------------------------------------------

const setKeyPattern = (set) => {
    if(set)
    {
        pattern = "^([a-zA-Z]){2,12}$";
        ke.setAttribute("pattern", pattern);
        ke.required = true;
    }
    else
    {
        ke.removeAttribute("pattern");
        ke.required =false;
        ke.value = "";
    }

};

//-------------------------------------------

const setTxtAttr = (val) => {
     switch (parseInt(val)) {
            case 1:
            txt.readOnly=true;
            txt.setAttribute("placeholder", "");
            txt.value= "";
            txtL.innerHTML = "";
            ra[0].checked = true;
            send.disabled=false;
            break;

            case 2:
            txt.removeAttribute('readonly')
            txt.value= "";
            txtL.innerHTML = "";
            txt.setAttribute("placeholder", `Input of source text for encryption (at least ${maxTxtLen} characters)`);
            ra[0].checked = true;
            send.disabled = true;
            break;

            case 3:
            txt.value= "";
            txtL.innerHTML = "";
            txt.removeAttribute('readonly');
            txt.setAttribute("placeholder", `Input of encrypted text for decryption (at least ${maxTxtLen} characters)`);
            ra[1].checked = true;
            setKeyPattern();
            send.disabled = true;
            break;
        }
};

//-------------------------------------------

const checKeyOption = (i, val) => {
    i -= 1;
    ra[i].checked = true;
    if(i==2){
        ke.value=val;
        ke.required = true;
    }
    else
    {
        ke.value="";
        ke.required = false;
    }
};

//-------------------------------------------

const checked = (checkAll) => {
    if(parseInt(checkAll))
        chAll.checked = true;

    for (let i = 0; i < ch.length; i++){
        if(val.includes(ch[i].value))
            ch[i].checked = true;
    }
}

//-------------------------------------------

const checkInputTxt = (i,val) => {
   i-=1;
   inputTxt[i].checked=true;
   if(i > 0)
   {
    txt.removeAttribute('readonly');
    txt.value = val;
    let dis = val.length >= maxTxtLen?false:true;
    //send.disabled=dis;
   }
   else
   {
     txt.readOnly=true;
   }
}

//-------------------------------------------
const checkTxtLen = () => {
    let L = txt.value.length;
    let message =  L > 0 ? `Entered ${L} characters` : "";
    txtL.innerHTML = message;
    let dis1 = L >= maxTxtLen?false:true;
    dis2 = true;
    if(!dis1) dis2 = validTxtByIC(txt.value);
    let dis = dis1 || dis2;
    if(dis)
        txtL.setAttribute("class","err");
   else
        txtL.removeAttribute("class");
    send.disabled = dis;
}

const getIndexCoincidence = (txt) => {
    txt = txt.replace(/[^a-zA-Z]/g, '').toUpperCase();
    let txtLen = txt.length;
    let L = txtLen * (txtLen - 1);
    if(L == 0) return 0;
    let rankLetters = txt.split("");
    let count = rankLetters.reduce((a,c) => (a[c] = ++a[c] || 1, a) ,{});
    let IC = 0;
    for (const n of Object.values(count))
        IC += (n * (n-1) )/L;
    return IC.toFixed(5);
}


const validTxtByIC = (txt)=>{
    let IC = getIndexCoincidence(txt);
    let message;
    if(IC < 0.06 && inputTxt[1].checked){
        message = "Attention!\nYou entered encrypted text instead of the source text.";
        txtL.innerHTML = message;
        return true;
    }
    else  if(IC > 0.06 && inputTxt[2].checked){
        message = "Attention!\nYou entered the source text instead of the encrypted text.";
        txtL.innerHTML =  message;
        return true;
    }
    else
        return false;
}

//-------------------------------------------

ra[2].addEventListener("click", (e)=>{
  setKeyPattern(1);
});


[0,1].forEach((i) => {
    ke.value="";
    ke.required = false;
    ra[i].addEventListener("click", (e)=>{
        setKeyPattern();
        if(i == 1)
        {
            inputTxt[2].checked =  true;
            setTxtAttr(3);
        }
        else
        {
            inputTxt[0].checked =  true;
            setTxtAttr(1);
            ke.value="";

        }
    });
});



for (var i = 0; i <inputTxt.length; i++) {
    if(inputTxt[i].checked)
        setTxtAttr(i+1);
    inputTxt[i].addEventListener("click", (e)=>{
       setTxtAttr(e.target.value);
    });
}



chAll.addEventListener("click", (e)=>{
    let check = e.target.checked;
    for (let i = 0; i < ch.length; i++){
        ch[i].checked = check;
    }
});

if(ra[2].checked){
   setKeyPattern(1);
}

if(ra[0].checked){
   ke.removeAttribute("pattern");
   ke.required = false;
}



txt.addEventListener("input", (e)=>{
    checkTxtLen();
});

//--------------------------------------------

