require('./bootstrap');

import Alpine from 'alpinejs';
import axios from 'axios';

Alpine.data('modal', () => ({
    contentData: {},
    open: false,
    loading: false,
    async openModal(id) {
        try {
            const url = `${window.location.protocol}//${window.location.hostname}/`
            const apiToken = this.$refs.token.value;
            var response = await axios.get(`${url}api/feedback/${id}`, {
                headers: {
                  'Authorization': `Bearer ${apiToken}`,
                }
            })
            this.open = true
            this.contentData = response.data    
        } catch (error) {
            
        }
    },
    closeModal() {
        this.open = false
    }
}));

Alpine.start()
