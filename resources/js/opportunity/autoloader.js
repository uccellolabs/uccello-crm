import { Kanban } from './kanban'

class Autoloader {
    constructor() {
        this.lazyLoad()
    }

    lazyLoad() {
        let page = $('meta[name="page"]').attr('content')

        switch (page) {
            case 'kanban':
                new Kanban()
                break;
        }
    }
}

new Autoloader()