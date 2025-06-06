import {Controller} from '@hotwired/stimulus';
export default class extends Controller {

    connect() {

        const modalContentOrder = document.querySelector('#modal-content-reservation');
        const pId = document.createElement('p')
        const pUsername = document.createElement('p')
        const pEmail = document.createElement('p')
        const pDate = document.createElement('p')
        const pStatus = document.createElement('p')
        const pMessage = document.createElement('p')

        document.querySelectorAll('.open-modal-reservation').forEach(button =>{
            button.addEventListener('click',()=>{

                pId.innerHTML = "<strong>ID: </strong>" +  button.dataset.id
                pUsername.innerHTML =  "<strong>Username: </strong>" + button.dataset.username
                pEmail.innerHTML = "<strong>Mail: </strong>" + button.dataset.email
                pDate.innerHTML = "<strong>Date: </strong>" + button.dataset.created_at
                pStatus.innerHTML = "<strong>Statut: </strong>" + button.dataset.status
                pMessage.innerHTML = "<strong>Message: </strong>" + button.dataset.message

                modalContentOrder.appendChild(pId)
                modalContentOrder.appendChild(pDate)
                modalContentOrder.appendChild(pUsername)
                modalContentOrder.appendChild(pEmail)
                modalContentOrder.appendChild(pStatus)
                modalContentOrder.appendChild(pMessage)

            } )
        })



    }
}