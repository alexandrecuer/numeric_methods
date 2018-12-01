//decker-brent
function fzero(f,a,b){
    var f_a = f(a);
    var f_b = f(b);
    if (f_a == 0) {
        return {zero: a,nbit: 0};
    }
    if (f_b == 0) {
        return {zero: b,nbit: 0};
    }
    if (Math.sign(f_a)==Math.sign(f_b)) {
        return {zero: "sign of f(a) and f(b) must be opposite",nbit: 0};
        }
    if (isNaN(f_a) || isNaN(f_b)) {
        return {zero: "a or b is NaN",nbit: 0};
        }
    var c = a;
    var f_c = f_a;
    var d = b-c;
    var e = d;
    var m;
    var tol;
    var s;
    var p;
    var q;
    var i = 0;
    while (f_b != 0) {
        console.log("***round "+i);
        console.log("next couple abc: ("+a+","+b+","+c+")");
        console.log("f values are: ("+f_a+","+f_b+","+f_c+")"); 
        if (Math.sign(f_a) == Math.sign(f_b)) {
            a = c ; f_a = f_c;
            d = b ; e = d;
        }
        if (Math.abs(f_a) < Math.abs(f_b)) {
            c = b ; b = a ; a = c;
            f_c = f_b ; f_b = f_a ; f_a = f_c;
        }
        m = 0.5*(a - b);
        console.log("the middle is "+m);
        tol = 2.0*Math.pow(2,-52)*Math.max(Math.abs(b),1.0);
        //tol = 2.0*0.0000000001*Math.max(Math.abs(b),1.0);
        if ((Math.abs(m) <= tol) || (f_b == 0.0)) {
            return {zero: b,nbit: i};
        }

        if (Math.abs(e) < tol || Math.abs(f_c) <= Math.abs(f_b)) {
            //bisection
            console.log("bisection");
            d = m ; 
            e = m ;
        } else {
            //interpolation 
            s = f_b/f_c;
            if (a == c) {
                console.log("linear interpolation");
                p = 2.0*m*s;
                q = 1.0 - s;
            } else {
                //inverse quadratic interpolation
                console.log("inverse quadratic interpolation");
                q = f_c / f_a;
                r = f_b / f_a;
                p = s*(2.0*m*q*(q - r) - (b - c)*(r - 1.0));
                q = (q - 1.0)*(r - 1.0)*(s - 1.0);
            }
            if (p > 0) q = -q; else p = -p;
            //interpolated point acceptable ?
            if ((2.0*p < 3.0*m*q - Math.abs(tol*q)) && p < Math.abs(0.5*e*q)) {
                e = d;
                d = p/q;
            } else {
                d = m;
                e = m;
            }
        }
        //next point
        i = i + 1;
        c = b;
        f_c = f_b;
        if (Math.abs(d) > tol){
            b = b + d;
        } else {
            b = b - Math.sign(b-a)*tol;
        }
        f_b = f(b);
        if (f_b == 0) {
            return {zero: b,nbit: i};
        }
    }
    
}