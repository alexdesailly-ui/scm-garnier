document.addEventListener('DOMContentLoaded',function(){
  // Mobile menu
  const hamburger=document.getElementById('hamburger');
  const nav=document.getElementById('nav');
  if(hamburger){
    hamburger.addEventListener('click',function(){
      nav.classList.toggle('open');
      this.setAttribute('aria-expanded',nav.classList.contains('open'));
    });
    document.addEventListener('click',function(e){
      if(!nav.contains(e.target)&&!hamburger.contains(e.target))nav.classList.remove('open');
    });
  }

  // Sticky header shadow
  const header=document.getElementById('header');
  if(header){
    window.addEventListener('scroll',function(){
      header.style.boxShadow=window.scrollY>10?'0 2px 20px rgba(0,0,0,.1)':'0 2px 12px rgba(0,0,0,.06)';
    });
  }

  // Booking form logic
  const bookingForm=document.getElementById('booking-form');
  if(bookingForm){
    window.currentStep=1;

    window.nextStep=function(step){
      if(step===3&&!validateStep2())return;
      if(step===4){
        if(!validateStep3())return;
        submitBooking();
        return;
      }
      showStep(step);
    };

    window.prevStep=function(step){showStep(step)};

    function showStep(step){
      for(let i=1;i<=4;i++){
        const el=document.getElementById('step-'+i);
        if(el)el.classList.toggle('hidden',i!==step);
      }
      document.querySelectorAll('.progress-step').forEach(function(el){
        const s=parseInt(el.dataset.step);
        el.classList.toggle('active',s===step);
        el.classList.toggle('done',s<step);
      });
      window.currentStep=step;
      window.scrollTo({top:document.querySelector('.booking-progress').offsetTop-100,behavior:'smooth'});
    }

    function validateStep2(){
      const date=document.getElementById('appointment-date').value;
      const time=document.getElementById('appointment-time').value;
      if(!date||!time){
        showError('Veuillez sélectionner une date et un horaire.');
        return false;
      }
      hideError();
      return true;
    }

    function validateStep3(){
      const ln=document.getElementById('patient-lastname').value.trim();
      const fn=document.getElementById('patient-firstname').value.trim();
      const em=document.getElementById('patient-email').value.trim();
      const ph=document.getElementById('patient-phone').value.trim();
      const consent=document.getElementById('consent-rgpd').checked;
      if(!ln||!fn||!em||!ph){showError('Veuillez remplir tous les champs obligatoires.');return false}
      if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)){showError('Adresse email invalide.');return false}
      if(!consent){showError('Veuillez accepter la politique de confidentialité.');return false}
      hideError();
      return true;
    }

    // Date change -> fetch slots
    const dateInput=document.getElementById('appointment-date');
    if(dateInput){
      dateInput.addEventListener('change',function(){
        fetchSlots(this.value);
      });
    }

    function fetchSlots(date){
      const container=document.getElementById('time-slots');
      const nurseId=document.querySelector('[name=nurse_id]');
      container.innerHTML='<p class="slots-placeholder">Chargement...</p>';
      document.getElementById('appointment-time').value='';
      document.getElementById('btn-step3').disabled=true;

      let url='/api/appointments.php?action=slots&date='+encodeURIComponent(date);
      if(nurseId&&nurseId.value)url+='&nurse_id='+encodeURIComponent(nurseId.value);

      fetch(url).then(r=>r.json()).then(function(data){
        if(!data.slots||data.slots.length===0){
          container.innerHTML='<p class="slots-placeholder">Aucun créneau disponible pour cette date.</p>';
          return;
        }
        container.innerHTML='';
        data.slots.forEach(function(slot){
          const btn=document.createElement('button');
          btn.type='button';
          btn.className='time-slot';
          btn.textContent=slot.replace(':','h');
          btn.addEventListener('click',function(){
            container.querySelectorAll('.time-slot').forEach(b=>b.classList.remove('selected'));
            this.classList.add('selected');
            document.getElementById('appointment-time').value=slot;
            document.getElementById('btn-step3').disabled=false;
          });
          container.appendChild(btn);
        });
      }).catch(function(){
        container.innerHTML='<p class="slots-placeholder">Erreur de chargement. Réessayez.</p>';
      });
    }

    // Home visit toggle
    const homeVisit=document.getElementById('home-visit');
    if(homeVisit){
      homeVisit.addEventListener('change',function(){
        const ag=document.getElementById('address-group');
        if(ag)ag.style.display=this.checked?'block':'none';
      });
    }

    function submitBooking(){
      const formData=new FormData(bookingForm);
      const errEl=document.getElementById('booking-error');
      errEl.classList.add('hidden');

      fetch('/api/appointments.php',{
        method:'POST',
        body:formData
      }).then(r=>r.json()).then(function(data){
        if(data.success){
          showStep(4);
          const det=document.getElementById('confirmation-details');
          det.innerHTML='<p><strong>Référence :</strong> '+data.reference+'</p>'
            +'<p><strong>Soin :</strong> '+data.care_type+'</p>'
            +'<p><strong>Date :</strong> '+data.date+'</p>'
            +'<p><strong>Heure :</strong> '+data.time+'</p>'
            +'<p><strong>Patient :</strong> '+data.patient+'</p>';
        }else{
          showError(data.error||'Une erreur est survenue.');
        }
      }).catch(function(){
        showError('Erreur de connexion. Veuillez réessayer.');
      });
    }

    function showError(msg){
      const el=document.getElementById('booking-error');
      el.textContent=msg;
      el.classList.remove('hidden');
      el.scrollIntoView({behavior:'smooth',block:'center'});
    }
    function hideError(){document.getElementById('booking-error').classList.add('hidden')}
  }

  // Contact form
  const contactForm=document.getElementById('contact-form');
  if(contactForm){
    contactForm.addEventListener('submit',function(e){
      e.preventDefault();
      const status=document.getElementById('contact-status');
      status.className='alert alert-success';
      status.textContent='Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.';
      status.classList.remove('hidden');
      this.reset();
    });
  }

  // Article category filter
  document.querySelectorAll('.filter-btn').forEach(function(btn){
    btn.addEventListener('click',function(){
      document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
      this.classList.add('active');
      const cat=this.dataset.category;
      document.querySelectorAll('.article-card').forEach(function(card){
        card.style.display=(cat==='general'||card.dataset.category===cat)?'':'none';
      });
    });
  });
});
