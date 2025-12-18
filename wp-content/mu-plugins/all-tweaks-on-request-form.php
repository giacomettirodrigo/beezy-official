<?php

add_action( 'template_redirect', 'check_and_replace_content_seamlessly', 1 );

add_action('wp_footer', function(){
    ?>
    <script>
    (function(){
        function applyRequestFormTweaks(form){
            if(!form || !form.classList.contains('hp-form--request-submit')) return;

            const formContainer = form.querySelector('.hp-form__fields');
            if(!formContainer || formContainer.hasAttribute('data-tweaks-applied')) return;

            const fieldData = {
                'categories': 'Choose the type of request',
                'title': '',
                'task_city': 'Choose the city',
                'task_date': '',
                'description': '',
                'budget': ''
            };

            const characterLimits = { 'title': 60, 'description': 800 };

            form.querySelectorAll('.hp-form__field--attachment-upload').forEach(wrapper=>{
                const input = wrapper.querySelector('input[type="file"][name]');
                if(input && (input.name === 'images' || input.getAttribute('data-name') === 'images')){
                    wrapper.remove();
                }
            });

            const fieldsInOrder = ['categories','title','task_city','task_date','description','budget'];
            fieldsInOrder.forEach(name=>{
                const field = form.querySelector(`.hp-form__field [name="${name}"]`);
                if(field){
                    const wrapper = field.closest('.hp-form__field');
                    if(wrapper) formContainer.appendChild(wrapper);
                }
            });

            Object.keys(fieldData).forEach(name=>{
                const field = form.querySelector(`.hp-form__field [name="${name}"]`);
                if(!field) return;

                if(field.tagName === 'SELECT' || field.type === 'select-one'){
                    if(field.value === '' && !field.querySelector('option[disabled][selected]')){
                        const opt = document.createElement('option');
                        opt.textContent = fieldData[name];
                        opt.value = '';
                        opt.disabled = true;
                        opt.selected = true;
                        field.prepend(opt);
                    }
                } else if((field.tagName === 'INPUT' || field.tagName === 'TEXTAREA') && !field.value){
                    field.value = fieldData[name];
                }

                if(characterLimits[name] && !field.hasAttribute('data-counter')){
                    const limit = characterLimits[name];
                    const wrapper = field.closest('.hp-form__field');
                    const counter = document.createElement('small');
                    counter.className = 'hp-field__description';
                    const update = ()=> counter.textContent = `${field.value.length}/${limit} characters`;
                    field.addEventListener('input', update);
                    wrapper.appendChild(counter);
                    update();
                    field.setAttribute('data-counter','true');
                }

                if(name === 'budget' && !field.hasAttribute('data-euro-note')){
                    const wrapper = field.closest('.hp-form__field');
                    const note = document.createElement('small');
                    note.textContent = '(value in Euros)';
                    note.className = 'hp-field__description';
                    wrapper.appendChild(note);
                    field.setAttribute('data-euro-note','true');
                }
            });

            formContainer.setAttribute('data-tweaks-applied','true');
        }


        document.addEventListener('DOMContentLoaded', function(){
            document.querySelectorAll('form.hp-form.hp-form--request-submit').forEach(f => applyRequestFormTweaks(f));

            const obs = new MutationObserver(()=> {
                document.querySelectorAll('form.hp-form.hp-form--request-submit').forEach(f => {
                    if(!f.hasAttribute('data-tweaks-applied')) applyRequestFormTweaks(f);
                });
            });
            obs.observe(document.body, { childList: true, subtree: true });
        });
    })();
    </script>
    <?php
});

