/* Menu collapse */
$(".menu-button").on("click", function () {
    $("#side-bar").toggleClass("collapsed");
    $("#content-container").toggleClass("col-sm-12 col-sm-10 col-xs-11 col-xs-12");
});

/* AngularJS */
var myApp = angular.module('myApp', ['angularUtils.directives.dirPagination', 'ui.bootstrap']);

// Factories
myApp.factory("LegislatorsFactory", ['$http', function ($http) {
    var obj = {};

    obj.getAllLegislators = function () {
        var req = {
            method: "GET",
            url: "../query.php",
            params: {
                type: 'legislators',
                per_page: 'all',
                apikey: '8254cc34a8b943bfb41d348cca173ccb'
            }
        };
        return $http(req);
    }
    return obj;
}]);

myApp.factory("BillsFactory", ['$http', function ($http) {
    var obj = {};

    obj.getActiveBills = function () {
        var req = {
            method: "GET",
            url: "../query.php",
            params: {
                type: 'bills',
                per_page: '50',
                active_bill: true,
                order: "introduced_on__desc",
                apikey: '8254cc34a8b943bfb41d348cca173ccb'
            }
        };
        return $http(req);
    }

    obj.getNewBills = function () {
        var req = {
            method: "GET",
            url: "../query.php",
            params: {
                type: 'bills',
                per_page: '50',
                active_bill: false,
                order: "introduced_on__desc",
                apikey: '8254cc34a8b943bfb41d348cca173ccb'
            }
        };
        return $http(req);
    }

    obj.getBillsByPerson = function (person) {
        var req = {
            method: "GET",
            url: "../query.php",
            params: {
                type: 'bills',
                per_page: '5',
                sponsor_id: person.bioguide_id,
                apikey: '8254cc34a8b943bfb41d348cca173ccb'
            }
        };
        return $http(req);
    }
    return obj;
}]);

myApp.factory("CommitteesFactory", ['$http', function ($http) {
    var obj = {};

    obj.getAllCommittees = function () {
        var req = {
            method: "GET",
            url: "../query.php",
            params: {
                type: 'committees',
                per_page: 'all',
                apikey: '8254cc34a8b943bfb41d348cca173ccb'
            }
        };
        return $http(req);
    }

    obj.getCommitteesByPerson = function (person) {
        var req = {
            method: "GET",
            url: "../query.php",
            params: {
                type: 'committees',
                per_page: '5',
                member_ids: person.bioguide_id,
                apikey: '8254cc34a8b943bfb41d348cca173ccb'
            }
        };
        return $http(req);
    }
    return obj;
}]);


// Filters
myApp.filter("firstLetter", function () {
    return function (word) {
        if (word.charAt(0).toLowerCase() == 'j') {
            return "s";
        } else {
            return word.charAt(0).toLowerCase();
        }
    };
});

myApp.filter('capitalize', function () {
    return function (input) {
        return (angular.isString(input)) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : input;
    }
});

myApp.filter('party', function () {
    return function (letter) {
        if (letter === "R")
            return "Republican";
        else if (letter === "D")
            return "Democrat";
        else
            return "Independent";
    }
});


/* ============================================================================== */
// Controllers
function LegiController($scope, $rootScope, $http, $q, LegislatorsFactory, BillsFactory, CommitteesFactory) {
    $scope.states = ['Alabama', 'Alaska', 'American Samoa', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'District of Columbia', 'Florida', 'Georgia', 'Guam', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Northern Mariana Islands', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Puerto Rico', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'US Virgin Islands', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'];

    $scope.currentPage1 = 1;
    $scope.currentPage2 = 1;
    $scope.currentPage3 = 1;
    $scope.pageSize = 10;
    $scope.dynamic = 80;
    $scope.fromPage = 0;


    //**************************** Local storage
    $rootScope.favedLegislators = localStorage.getItem("favedLegislators") !== null ? JSON.parse(localStorage.getItem("favedLegislators")) : [];
    $rootScope.favedBills = localStorage.getItem("favedBills") !== null ? JSON.parse(localStorage.getItem("favedBills")) : [];
    $rootScope.favedCommittees = localStorage.getItem("favedCommittees") !== null ? JSON.parse(localStorage.getItem("favedCommittees")) : [];


    $scope.starStyle = {};

    LegislatorsFactory.getAllLegislators().then(function (response) {
        $scope.jsonObj = (response.data);
    }, function (response) {
        alert("Unable to fetch data from AWS, please try again later.");
    });
	
    /* Filters */
    /* State filter */
    $scope.stateFilter = function (person) {
        if (!$scope.queryState)
            return true;
        return angular.equals(person.state_name, $scope.queryState);
    };
    /* House name filter */
    $scope.nameFilterHouse = function (person) {
        var fullName1 = person.first_name + " " + person.last_name;
        var fullName2 = person.last_name + ", " + person.first_name;
        return (angular.lowercase(fullName1).indexOf(angular.lowercase($scope.queryHouse) || '') !== -1 ||
            angular.lowercase(fullName2).indexOf(angular.lowercase($scope.queryHouse) || '') !== -1);
    };
    /* Senate name filter */
    $scope.nameFilterSenate = function (person) {
        var fullName1 = person.first_name + " " + person.last_name;
        var fullName2 = person.last_name + ", " + person.first_name;
        return (angular.lowercase(fullName1).indexOf(angular.lowercase($scope.querySenate) || '') !== -1 ||
            angular.lowercase(fullName2).indexOf(angular.lowercase($scope.querySenate) || '') !== -1);
    };


    /* Functions */
    $scope.legiDetail = function (person, from) {
        $rootScope.personDetail = person;
        $rootScope.fromPage = from;

        $q.all([BillsFactory.getBillsByPerson(person).then(function (responseB) {
                $rootScope.topBills = (responseB.data);
            }),
            CommitteesFactory.getCommitteesByPerson(person).then(function (responseC) {
                $rootScope.topCom = (responseC.data);
            })]).then(function () {
            var totalTime = Date.parse($rootScope.personDetail.term_end) - Date.parse($rootScope.personDetail.term_start);
            var usedTime = Date.now() - Date.parse($rootScope.personDetail.term_start);
            $rootScope.term = Math.round(usedTime / totalTime * 100);
            jQuery('#legiCarousel.carousel').carousel(1);
        });
    };

    $scope.goBack = function () {
        jQuery('#legiCarousel.carousel').carousel(0);
        /*
        if ($rootScope.fromPage == 1) {
            jQuery('#legiCarousel.carousel').one('slid.bs.carousel', function (e) {
                //console.log("Time to go back");
                jQuery('#sideTab a:last').tab('show');
            });
        }
        */
    }



    $scope.toogleFavorite = function () {
        if (!$scope.inFavList()) {
            $rootScope.favedLegislators.push($rootScope.personDetail);
        } else {
            $scope.removeFromFav();
        }
        localStorage.setItem('favedLegislators', JSON.stringify($rootScope.favedLegislators));
    };

    $scope.removeFromFav = function () {
        angular.forEach($rootScope.favedLegislators, function (value, index) {
            if (value.bioguide_id == $rootScope.personDetail.bioguide_id) {
                $rootScope.favedLegislators.splice(index, 1);
            }
        });
    }

    $scope.inFavList = function () {
        var ret = false;
        if ($rootScope.personDetail) {
            angular.forEach($rootScope.favedLegislators, function (value, index) {
                if (value.bioguide_id == $rootScope.personDetail.bioguide_id) {
                    ret = true;
                    return;
                }
            });
        }
        return ret;
    };

};

function BillController($scope, $rootScope, $http, BillsFactory) {

    $scope.currentPage1 = 1;
    $scope.currentPage2 = 1;
    $scope.currentPage3 = 1;
    $scope.pageSize = 10;

    BillsFactory.getActiveBills().then(function (response) {
        $scope.activeBills = (response.data);
    }, function (response) {
        alert("Unable to fetch data from AWS, please try again later.");
    });

    BillsFactory.getNewBills().then(function (response) {
        $scope.newBills = (response.data);
    }, function (response) {
        alert("Unable to fetch data from AWS, please try again later.");
    });


    /* Functions */
    $scope.viewBill = function (bill, from) {
        $rootScope.billDetail = bill;
        $rootScope.fromPage = from;
        jQuery('#billCarousel.carousel').carousel(1);
    };

    $scope.goBack = function () {
        jQuery('#billCarousel.carousel').carousel(0);
        /*
                if ($rootScope.fromPage == 1) {
                    jQuery('#billCarousel.carousel').one('slid.bs.carousel', function (e) {
                        //console.log("Time to go back");
                        jQuery('#sideTab a:last').tab('show');
                    });
                }
                */
    };

    $scope.toogleFavorite = function () {
        if (!$scope.inFavList()) {
            $rootScope.favedBills.push($rootScope.billDetail);
        } else {
            $scope.removeFromFav();
        }
        localStorage.setItem('favedBills', JSON.stringify($rootScope.favedBills));
    };

    $scope.removeFromFav = function () {
        angular.forEach($rootScope.favedBills, function (value, index) {
            if (value.bill_id == $rootScope.billDetail.bill_id) {
                $rootScope.favedBills.splice(index, 1);
            }
        });
    }

    $scope.inFavList = function () {
        var ret = false;
        if ($rootScope.billDetail) {
            angular.forEach($rootScope.favedBills, function (value, index) {
                if (value.bill_id == $rootScope.billDetail.bill_id) {
                    ret = true;
                    return;
                }
            });
        }
        return ret;
    };

}

function CommitteeController($scope, $rootScope, $http, CommitteesFactory) {

    $scope.currentPage1 = 1;
    $scope.currentPage2 = 1;
    $scope.currentPage3 = 1;
    $scope.pageSize = 10;

    CommitteesFactory.getAllCommittees().then(function (response) {
        $scope.jsonObj = (response.data);
    }, function (response) {
        alert("Unable to fetch data from AWS, please try again later.")
    });

    $scope.toogleFavorite = function (comDetail) {
        if (!$scope.inFavList(comDetail)) {
            $rootScope.favedCommittees.push(comDetail);
        } else {
            $scope.removeFromFav(comDetail);
        }
        localStorage.setItem('favedCommittees', JSON.stringify($rootScope.favedCommittees));
    };

    $scope.removeFromFav = function (comDetail) {
        angular.forEach($rootScope.favedCommittees, function (value, index) {
            if (value.committee_id == comDetail.committee_id) {
                $rootScope.favedCommittees.splice(index, 1);
            }
        });
    }

    $scope.inFavList = function (comDetail) {
        var ret = false;
        angular.forEach($rootScope.favedCommittees, function (value, index) {
            if (value.committee_id == comDetail.committee_id) {
                ret = true;
                return;
            }
        });
        return ret;
    };
}


function favController($scope, $rootScope, $http, $q, LegislatorsFactory, BillsFactory, CommitteesFactory) {

    /* Functions */
    $scope.legiDetail = function (person, from) {
        $rootScope.personDetail = person;
        $rootScope.fromPage = from;

        $q.all([BillsFactory.getBillsByPerson(person).then(function (responseB) {
                $rootScope.topBills = (responseB.data);
            }),
            CommitteesFactory.getCommitteesByPerson(person).then(function (responseC) {
                $rootScope.topCom = (responseC.data);
            })]).then(function () {
            var totalTime = Date.parse($rootScope.personDetail.term_end) - Date.parse($rootScope.personDetail.term_start);
            var usedTime = Date.now() - Date.parse($rootScope.personDetail.term_start);
            $rootScope.term = Math.round(usedTime / totalTime * 100);
            jQuery('#legiCarousel.carousel').carousel(0);
            jQuery('#sideTab a:first').tab('show');
            jQuery('#sideTab a:first').one('shown.bs.tab', function () {
                jQuery('#legiCarousel.carousel').carousel(1);
            });
        });
    };

    $scope.viewBill = function (bill, from) {
        $rootScope.fromPage = 1;
        $rootScope.billDetail = bill;
        jQuery('#billCarousel.carousel').carousel(0);
        jQuery('#sideTab li:eq(1) a').tab('show');
        jQuery('#sideTab li:eq(1) a').one('shown.bs.tab', function () {
<<<<<<< HEAD
                jQuery('#billCarousel.carousel').carousel(1);
            });
       // jQuery('#billCarousel.carousel').carousel(1);
=======
            jQuery('#billCarousel.carousel').carousel(1);
        });
        // jQuery('#billCarousel.carousel').carousel(1);
>>>>>>> a2127bdfff648c9c38f95b650fcfa8b01c344bb8
    };


    $scope.removeFavCom = function (favCom) {
        angular.forEach($rootScope.favedCommittees, function (value, index) {
            if (value.committee_id == favCom.committee_id) {
                $rootScope.favedCommittees.splice(index, 1);
            }
        });
        localStorage.setItem('favedCommittees', JSON.stringify($rootScope.favedCommittees));
    };

    $scope.removeFavBill = function (favBill) {
        angular.forEach($rootScope.favedBills, function (value, index) {
            if (value.bill_id == favBill.bill_id) {
                $rootScope.favedBills.splice(index, 1);
            }
        });
        localStorage.setItem('favedBills', JSON.stringify($rootScope.favedBills));
    };

    $scope.removeFavLegi = function (favLegi) {
        angular.forEach($rootScope.favedLegislators, function (value, index) {
            if (value.bioguide_id == favLegi.bioguide_id) {
                $rootScope.favedLegislators.splice(index, 1);
            }
        });
        localStorage.setItem('favedLegislators', JSON.stringify($rootScope.favedLegislators));
    };

}


// Bind Controllers
myApp.controller('LegiController', LegiController);
myApp.controller('BillController', BillController);
myApp.controller('CommitteeController', CommitteeController);
myApp.controller('favController', favController);