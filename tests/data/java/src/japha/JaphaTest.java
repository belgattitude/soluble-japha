package japha;

public class JaphaTest {

    public static void main(String[] args) {
        System.out.println("Just a soluble-japha test file used for tests");
    }    
    
    
    public static String testStaticReturnString(String str) {
        return str;
    }

    public void testSystemOutPrintln(String str) {
        System.out.println(str);   
    }    
    
    
    public String testThrowException(String str) throws Exception {
        throw new Exception("Always return and exception with string " + str);
    }
    

}
