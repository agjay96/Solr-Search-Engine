import java.io.BufferedWriter;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileWriter;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashSet;
import java.util.Set;

import org.apache.tika.exception.TikaException;
import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.ParseContext;
import org.apache.tika.parser.html.HtmlParser;
import org.apache.tika.sax.BodyContentHandler;
import org.xml.sax.SAXException;



public class bigText {
	
	public static void defWriteToFile(ArrayList<String> wordList) throws IOException
	{
		BufferedWriter writer = new BufferedWriter(new FileWriter("big.txt"));
		for(String x: wordList)
		{
		    writer.write(x+"\n");
		}
	}
	public static void parseFiles(String directoryPath)throws FileNotFoundException, IOException, SAXException, TikaException
	{
        File dir = new File(directoryPath);
        File[] files = dir.listFiles();
        int i =0;
        ArrayList<String> fullList = new ArrayList();
        for(File x: files)
        {
        	//Set<String> temp = ;
        	fullList.addAll(parseFile(x));
		System.out.println("It is goin on");
        	//i++;
        	//if(i == 10)
        		//break;
        }
        defWriteToFile(fullList);
	}
	
	public static ArrayList<String> parseFile(File myFile) throws FileNotFoundException, IOException, SAXException, TikaException
	{
	      BodyContentHandler handler = new BodyContentHandler(-1);
	      Metadata metadata = new Metadata();
	      FileInputStream inputstream = new FileInputStream(myFile);
	      ParseContext pcontext = new ParseContext();
	      //Html parser 
	      HtmlParser htmlparser = new HtmlParser();
	      htmlparser.parse(inputstream, handler, metadata,pcontext);
	      String temp = handler.toString();
	      //temp =temp.replace("\\[.,\\/#!$%\\^&\\*;:{}=\\-_`~()\\]","");
	      String myString = handler.toString();
	      ArrayList bigList = new ArrayList(Arrays.asList(myString.split("\\W+")));
	      //System.out.println(bigList);
	      return bigList;
	}
	
	public static void main(String args[]) throws FileNotFoundException, IOException, SAXException, TikaException 
		{
			String directoryPath= "/home/agjayasree/Downloads/nytimes/";
			parseFiles(directoryPath);
		}
		// TODO Auto-generated catch bloc
}
