package JaphaTest;

import java.util.*;

public class JaphaTestEnumSet {

    public enum Numbers {
        ONE, TWO, THREE, FOUR, FIVE
    };

    public static void main(String[] args) {

        // create a set
        EnumSet<Numbers> set;

        // add one element
        set = EnumSet.of(Numbers.FIVE);

        // print the set
        System.out.println("Set:" + set);

        // add another element which replaces the previous
        set = EnumSet.of(Numbers.THREE);

        // print the set. Notice that it has one element
        System.out.println("Set:" + set);
    }
}
