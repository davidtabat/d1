AuItL = {
    isReady: function () {
        return true
    }, initial: function (d) {
        var THIS=this;
        this.id = d.e;
        this.lic = $(this.id).value;
        this.mod = d.m;
        var c = BLANK_IMG.split("/");
        c.pop();
        c = c.join("/") + "/auit/snm-pdf/widgets/avi.swf";
        if (window.BASE_URL) {
            var a = BASE_URL.indexOf("/index.php/");
            if (a >= 0) {
                c = BASE_URL.substr(0, a);
                c += "/js/auit/snm-pdf/widgets/avi.swf"
            }
        }
        this["m"] = function () {
            var b = (this.x ? "remove" : "add") + "ClassName";
            $("config_edit_form").select(".auit-no-display").each(function (f) {
                f[b]("no-display")
            });
            if (this.lic && !this["x"]) {
                alert(d.f)
            }
        };
        this.b = function () {
            AuItL.lic = $(AuItL.id).value;
            if ( AuItL.lic.length > 0 )
            {
                AuItL.x=AuItL.lic.split('-').length == 4 && AuItL.lic.length==19;
                AuItL.m();
            }
        }
        /*
        this.flexContainerId = new Element("div", {style: ""});
        $("config_edit_form").insert(this.flexContainerId);
        this.flex = new Flex.Object({width: 1, height: 1, src: c, allowscriptaccess: "always"});
        this.flex.apply(this.flexContainerId);
        */
        Event.observe($(this.id), "change", function () {
            AuItL.b();
            //AuItL.lic = $(AuItL.id).value;
            //var b = document[AuItL.flex.getAttribute("id")];
            //b.check()
            //AuItL.x=AuItL.lic.split('-').length == 4;
            //AuItL.m();
        });
        AuItL.b();
    }, init: function () {
        try {
            AuItL.m()
        } catch (a) {
        }
    }
};