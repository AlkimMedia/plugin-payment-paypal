Vue.component('ppExpressButton', {
    template: '#vue-pp-button-component',

    props: [
        "lang"
    ],

    data: function() {
        return {
            ppButtonElement: ''
        };
    },

    methods: {
        submitButton: function() {
            var component = this;

        },

        renderDeButton: function() {

        }
    },

    ready: function() {
        if (document.getElementById('ppButton')) {
            this.ppButtonElement = document.getElementById('ppButton');

            switch (this.lang) {
                case 'de':
                    var source = '/resources/buttons/paypal_express_de.png';
                    this.ppButtonElement.style.backgroundImage = "url('"+ source +"')";
                    break;

                case 'en':

                    break;

                default:

            }
        }
    }
});
