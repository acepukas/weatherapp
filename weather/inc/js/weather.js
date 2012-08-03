(function() {

    var Station = Backbone.Model.extend({
        urlRoot : '/weather/api/' 
    });

    var Stations = Backbone.Collection.extend({
        model : Station
    });

    var stationIds = (function(){
        var ids = ['458','141','430'],
            pointer = 0;
        
        return {
            next : function(){
                return ids[pointer = ((++pointer) % ids.length)];
            },

            prev : function() {
                return ids[pointer = (ids.length + --pointer) % ids.length];
            },

            current : function() {
                return ids[pointer];
            }
        };

    }());

    var StationView = Backbone.View.extend({
        initialize : function() {
            this.collection.bind('add',this.addModel,this);
            this.selectModel(stationIds.current());
        },

        el:$('#station_container'),

        template : _.template($('#station_data').html()),
        
        render : function(model) {
            this.$el.html(this.template(model.toJSON()));
        },

        events : {
            'click .prev' : 'prev',
            'click .next' : 'next'
        },

        addModel : function(model) {
            var that = this,
                loadIndicator = $('#load-indicator');
            loadIndicator.toggleClass('hidden');
            model.fetch({
                success:function(){
                    loadIndicator.toggleClass('hidden');
                    that.render(model); 
                }
            });
        },

        prev : function(e) {
            var id = stationIds.prev();
            this.selectModel(id);
        },

        next : function(e) {
            var id = stationIds.next();
            this.selectModel(id);
        },

        selectModel : function(id) {
            var model = this.collection.get({'id':id});
            if(!!model) {
                this.render(model); 
            } else {
                this.collection.add(new Station({'id':id})); 
            }
        }
    });

    new StationView({collection:new Stations()});

}());
