import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    connect() {

        const modalContentModal = document.querySelector('#modal-content-show');
        const pDate = document.createElement('p');
        const pUsername = document.createElement('p')
        const pEmail = document.createElement('p')
        const pCreation = document.createElement('p')
        const pComment = document.createElement('p')

        let currentId = ''
        let commentValidate = ''


        /**
         * A DOM element representing the component or element with the ID 'showModal'.
         * Typically used to reference a modal dialog or similar UI component.
         *
         * @type {HTMLElement | null}
         */
        const modalShow = document.getElementById('showModal')
        modalShow.addEventListener('hidden.bs.modal', () => {
            const buttonValidate = document.querySelector('#modalValidateBtn')
            buttonValidate.removeAttribute('disabled')
        })


        document.querySelectorAll('.open-modal-show').forEach(button => {
            button.addEventListener('click', () => {

                currentId = button.dataset.id
                pDate.innerHTML = "<strong>Date: </strong>" + button.dataset.created_at
                pUsername.innerHTML = "<strong>Nom: </strong>" + button.dataset.username
                pEmail.innerHTML = "<strong>Mail: </strong>" + button.dataset.email
                pCreation.innerHTML = "<strong>Nom de la création: </strong>" + button.dataset.creation
                pComment.innerHTML = "<strong>Commentaire: </strong>" + button.dataset.message
                modalContentModal.appendChild(pDate)
                modalContentModal.appendChild(pUsername)
                modalContentModal.appendChild(pEmail)
                modalContentModal.appendChild(pCreation)
                modalContentModal.appendChild(pComment)

                commentValidate = button.dataset.validated;
                const buttonValidate = document.querySelector('#modalValidateBtn')

                if (commentValidate === '1') {
                    buttonValidate.setAttribute('disabled', 'disabled')
                }

            })
        })


        /**
         * References the 'Delete' button element within the modal dialog.
         * This button is typically used to trigger the deletion functionality
         * within the application when interacting with a modal.
         */
        const buttonDelete = document.querySelector('#modalDeleteBtn')
        buttonDelete.addEventListener('click', () => {
            if (currentId !== '') {
                fetch(`/admin/comments/${currentId}`, {
                    method: 'DELETE',
                })
                    .then(r => {
                        if (r.status === 200) {
                            window.location.href = '/admin/comments/'
                        } else {
                            alert(r.responseText)
                        }
                    }).catch(err => console.log(err))
            }
        })


        /**
         * A reference to the HTML button element with the ID 'modalValidateBtn'.
         * This variable is used to represent and interact with the validation button element
         * available in the document, typically associated with modal operations.
         */
        const buttonValidate = document.querySelector('#modalValidateBtn')
        buttonValidate.addEventListener('click', () => {
            if (currentId !== '') {
                let url = buttonValidate.dataset.url.replace('__ID__', currentId)
                console.log(url)
                fetch( url , {
                    method: 'POST',
                }).then(r => {
                    if (r.status === 200) {
                        window.location.href = '/admin/comments/'
                    } else {
                        alert(r.responseText)
                    }
                }).catch(err => console.log(err))
            }
        })
    }
}
